<?php
declare(strict_types=1);

namespace Litipk\BigNumbers;

use Decimal\Decimal as AdaptedDecimal;

/**
 * Immutable object that represents a rational number
 *
 * @author Andreu Correa Casablanca <castarco@litipk.com>
 */
class Decimal
{

    /**
     * @var AdaptedDecimal
     */
    protected $innerValue;

    /**
     * @var int Number of decimal places when converting to string
     */
    private $fixedScale;

    private function __construct(
        AdaptedDecimal $innerValue,
        ?int $fixedScale
    )
    {
        $this->innerValue = $innerValue;
        $this->fixedScale = $fixedScale;
    }

    /**
     * Decimal "constructor".
     *
     * @param  mixed $value
     * @param  int   $scale
     * @return Decimal
     */
    public static function create($value, int $scale = null): Decimal
    {
        if (\is_int($value) || \is_string($value)) {
            return new self(new AdaptedDecimal($value), $scale);
        }
        if (\is_float($value)) {
            $convertedValue = str_replace(',', '.', (string)$value);
            return new self(new AdaptedDecimal($convertedValue), $scale);
        }
        if ($value instanceof self) {
            return new self($value->innerValue, $scale);
        }
        if ($value instanceof AdaptedDecimal) {
            return new self($value, $scale);
        }
        throw new \TypeError(
            'Expected (int, float, string, Decimal), but received ' .
            (\is_object($value) ? \get_class($value) : \gettype($value))
        );
    }

    public static function fromInteger(int $intValue): Decimal
    {
        return self::create($intValue);
    }

    /**
     * @param  float $fltValue
     * @param  int   $scale
     * @return Decimal
     */
    public static function fromFloat(float $fltValue, int $scale = null): Decimal
    {
        return self::create($fltValue, $scale);
    }

    /**
     * @param  string  $strValue
     * @param  integer $scale
     * @return Decimal
     */
    public static function fromString(string $strValue, int $scale = null): Decimal
    {
        return self::create($strValue, $scale);
    }

    /**
     * Constructs a new Decimal object based on a previous one,
     * but changing it's $scale property.
     *
     * @param  Decimal  $decValue
     * @param  null|int $scale
     * @return Decimal
     */
    public static function fromDecimal(Decimal $decValue): Decimal
    {
        return self::create($decValue->innerValue);
    }

    /**
     * Adds two Decimal objects
     * @param  Decimal  $b
     * @return Decimal
     */
    public function add(Decimal $b): Decimal
    {
        return self::create($this->innerValue->add($b->innerValue));
    }

    /**
     * Subtracts two BigNumber objects
     * @param  Decimal $b
     * @return Decimal
     */
    public function sub(Decimal $b): Decimal
    {
        return self::create($this->innerValue->sub($b->innerValue));
    }

    /**
     * Multiplies two BigNumber objects
     * @param  Decimal $b
     * @return Decimal
     */
    public function mul(Decimal $b): Decimal
    {
        return self::create($this->innerValue->mul($b->innerValue));
    }

    /**
     * Divides the object by $b .
     * Warning: div with $scale == 0 is not the same as
     *          integer division because it rounds the
     *          last digit in order to minimize the error.
     *
     * @param  Decimal $b
     * @param  integer $scale
     * @return Decimal
     */
    public function div(Decimal $b): Decimal
    {
        if ($b->innerValue->isZero()) {
            throw new \DomainException("Division by zero is not allowed.");
        }
        if ($this->innerValue->isZero()) {
            return DecimalConstants::Zero();
        }
        return self::create($this->innerValue->div($b->innerValue));
    }

    /**
     * Returns the square root of this object
     * @return Decimal
     */
    public function sqrt(): Decimal
    {
        if ($this->innerValue->isNegative()) {
            throw new \DomainException(
                "Decimal can't handle square roots of negative numbers (it's only for real numbers)."
            );
        }
        if ($this->innerValue->isZero()) {
            return DecimalConstants::Zero();
        }
        return self::create($this->innerValue->sqrt());
    }

    /**
     * Powers this value to $b
     *
     * @param  Decimal  $b      exponent
     * @return Decimal
     */
    public function pow(Decimal $b): Decimal
    {
        if ($this->innerValue->isZero()) {
            if ($b->innerValue->isPositive()) {
                return self::fromDecimal($this);
            }
            throw new \DomainException("zero can't be powered to zero or negative numbers.");
        }
        return self::create($this->innerValue->pow($b->innerValue));
    }

    /**
     * Returns the object's logarithm in base 10
     * @param  integer $scale
     * @return Decimal
     */
    public function log10(): Decimal
    {
        if ($this->innerValue->isNegative()) {
            throw new \DomainException(
                "Decimal can't handle logarithms of negative numbers (it's only for real numbers)."
            );
        }
        if ($this->innerValue->isZero()) {
            throw new \DomainException(
                "Decimal can't represent infinite numbers."
            );
        }
        return self::create($this->innerValue->log10());
    }

    public function isZero(): bool
    {
        return $this->innerValue->isZero();
    }

    public function isPositive(): bool
    {
        return $this->innerValue->isPositive();
    }

    public function isNegative(): bool
    {
        return $this->innerValue->isNegative();
    }

    public function isInteger(): bool
    {
        return $this->innerValue->isInteger();
    }

    /**
     * Equality comparison between this object and $b
     * @param  Decimal $b
     * @return boolean
     */
    public function equals(Decimal $b): bool
    {
        return $this->innerValue->equals($b->innerValue);
    }

    /**
     * $this > $b : returns 1 , $this < $b : returns -1 , $this == $b : returns 0
     *
     * @param  Decimal $b
     * @param  integer $scale
     * @return integer
     */
    public function comp(Decimal $b): int
    {
        return $this->innerValue->compareTo($b->innerValue);
    }


    /**
     * Returns true if $this > $b, otherwise false
     *
     * @param  Decimal $b
     * @param  integer $scale
     * @return bool
     */
    public function isGreaterThan(Decimal $b): bool
    {
        return $this->comp($b) === 1;
    }

    /**
     * Returns true if $this >= $b
     *
     * @param  Decimal $b
     * @param  integer $scale
     * @return bool
     */
    public function isGreaterOrEqualTo(Decimal $b): bool
    {
        $comparisonResult = $this->comp($b);

        return $comparisonResult === 1 || $comparisonResult === 0;
    }

    /**
     * Returns true if $this < $b, otherwise false
     *
     * @param  Decimal $b
     * @param  integer $scale
     * @return bool
     */
    public function isLessThan(Decimal $b): bool
    {
        return $this->comp($b) === -1;
    }

    /**
     * Returns true if $this <= $b, otherwise false
     *
     * @param  Decimal $b
     * @param  integer $scale
     * @return bool
     */
    public function isLessOrEqualTo(Decimal $b): bool
    {
        $comparisonResult = $this->comp($b);

        return $comparisonResult === -1 || $comparisonResult === 0;
    }

    /**
     * Returns the element's additive inverse.
     * @return Decimal
     */
    public function additiveInverse(): Decimal
    {
        return self::create($this->innerValue->negate());
    }

    /**
     * Rounds the Decimal to have at most $scale digits after the point.
     * Internally, the default rounding method Decimal::ROUND_HALF_EVEN is used (halfway ties to nearest even number).
     * @param  integer $scale
     * @return Decimal
     */
    public function round(int $scale = 0): Decimal
    {
        return self::create($this->innerValue->round($scale));
    }

    /**
     * "Ceils" the Decimal to have at most $scale digits after the point
     * @return Decimal
     */
    public function ceil(): Decimal
    {
        return self::create($this->innerValue->ceil());
    }

    /**
     * "Floors" the Decimal to have at most $scale digits after the point
     * @return Decimal
     */
    public function floor(): Decimal
    {
        return self::create($this->innerValue->floor());
    }

    /**
     * Returns the absolute value (always a positive number)
     * @return Decimal
     */
    public function abs(): Decimal
    {
        return self::create($this->innerValue->abs());
    }

    /**
     * Calculate modulo with a decimal
     * @param Decimal $d
     * @param integer $scale
     * @return $this % $d
     */
    public function mod(Decimal $d): Decimal
    {
        return self::create($this->innerValue->mod($d->innerValue));
    }

    /**
     * Returns exp($this), said in other words: e^$this .
     *
     * @param integer $scale
     * @return Decimal
     */
    public function exp(): Decimal
    {
        return self::create($this->innerValue->exp());
    }

    /**
     * Indicates if the passed parameter has the same sign as the method's bound object.
     *
     * @param Decimal $b
     * @return bool
     */
    public function hasSameSign(Decimal $b): bool
    {
        return ($this->isPositive() && $b->isPositive()) || ($this->isNegative() && $b->isNegative());
    }

    public function asFloat(): float
    {
        return (float)$this->innerValue;
    }

    public function asInteger(): int
    {
        return (int)$this->innerValue;
    }

    public function innerValue(): string
    {
        if ($this->fixedScale === null) {
            return $this->innerValue->toString();
        }
        return $this->innerValue->toFixed($this->fixedScale);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->innerValue();
    }

}
