<?php

namespace Karser\Recaptcha3Bundle\Validator\Constraints;

use Karser\Recaptcha3Bundle\Services\IpResolverInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class Recaptcha3Validator extends ConstraintValidator
{
    /** @var ReCaptcha */
    private $recaptcha;
    private $enabled;
    private $ipResolver;

    public function __construct($recaptcha, $enabled, IpResolverInterface $ipResolver)
    {
        $this->recaptcha = $recaptcha;
        $this->enabled = $enabled;
        $this->ipResolver = $ipResolver;
    }

    public function validate($value, Constraint $constraint)
    {
        if ($value !== null && !is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }
        if (!$constraint instanceof Recaptcha3) {
            throw new UnexpectedTypeException($constraint, "Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3");
        }
        if (!$this->enabled) {
            return;
        }
        $value = null !== $value ? $value : '';
        $this->validateCaptcha($value, $constraint);
    }

    private function validateCaptcha($value, Recaptcha3 $constraint)
    {
        if ($value === '') {
            $this->buildViolation($constraint->messageMissingValue, $value);
            return;
        }
        $ip = $this->ipResolver->resolveIp();
        $response = $this->recaptcha->verify($value, $ip);
        if (!$response->isSuccess()) {
            $errorCodes = implode('; ', array_map(array($this, 'getErrorMessage'), $response->getErrorCodes()));
            $this->buildViolation($constraint->message, $value, $errorCodes);
        }
    }

    private function getErrorMessage($errorCode)
    {
        $messages = array(
            'missing-input-secret' => 'The secret parameter is missing',
            'invalid-input-secret' => 'The secret parameter is invalid or malformed',
            'missing-input-response' => 'The response parameter is missing',
            'invalid-input-response' => 'The response parameter is invalid or malformed',
            'bad-request' => 'The request is invalid or malformed',
            'timeout-or-duplicate' => 'The response is no longer valid: either is too old or has been used previously',
            'challenge-timeout' => 'Challenge timeout',
            'score-threshold-not-met' => 'Score threshold not met',
            'bad-response' => 'Did not receive a 200 from the service',
            'connection-failed' => 'Could not connect to service',
            'invalid-json' => 'Invalid JSON received',
            'unknown-error' => 'Not a success, but no error codes received',
            'hostname-mismatch' => 'Expected hostname did not match',
            'apk_package_name-mismatch' => 'Expected APK package name did not match',
            'action-mismatch' => 'Expected action did not match',
        );
        return array_key_exists($errorCode, $messages) ? $messages[$errorCode] : '';
    }

    private function buildViolation($message, $value, $errorCodes = '')
    {
        $this->context->addViolation($message, array(
            '{{ value }}' => $this->formatValue($value),
            '{{ errorCodes }}' => $this->formatValue($errorCodes),
            null,
            null,
            Recaptcha3::INVALID_FORMAT_ERROR
        ));
    }

    private function formatValue($value)
    {
        if ($value instanceof \DateTime) {
            if (class_exists('IntlDateFormatter')) {
                $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT, 'UTC');

                return $formatter->format(new \DateTime(
                    $value->format('Y-m-d H:i:s.u'),
                    new \DateTimeZone('UTC')
                ));
            }

            return $value->format('Y-m-d H:i:s');
        }

        if (\is_object($value)) {
            if (method_exists($value, '__toString')) {
                return $value->__toString();
            }

            return 'object';
        }

        if (\is_array($value)) {
            return 'array';
        }

        if (\is_string($value)) {
            return '"'.$value.'"';
        }

        if (\is_resource($value)) {
            return 'resource';
        }

        if (null === $value) {
            return 'null';
        }

        if (false === $value) {
            return 'false';
        }

        if (true === $value) {
            return 'true';
        }

        return (string) $value;
    }
}
