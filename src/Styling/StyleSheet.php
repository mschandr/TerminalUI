<?php

namespace TerminalUI\Styling;

/**
 * CSS-like StyleSheet for Terminal UI
 *
 * Allows defining styles with familiar CSS-like syntax:
 *
 * $style = StyleSheet::create([
 *     'width' => '50%',
 *     'height' => 20,
 *     'background' => Color::BLUE,
 *     'foreground' => Color::WHITE,
 *     'border' => 'double',
 *     'border-color' => Color::CYAN,
 *     'padding' => 1,
 *     'margin' => 2,
 *     'position' => 'absolute',
 *     'top' => 5,
 *     'left' => 10,
 *     'font-weight' => 'bold',
 *     'text-align' => 'center',
 * ]);
 */
class StyleSheet
{
    protected array $rules = [];

    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
    }

    /**
     * Create a new stylesheet
     */
    public static function create(array $rules = []): self
    {
        return new self($rules);
    }

    /**
     * Set a style property
     */
    public function set(string $property, mixed $value): self
    {
        $this->rules[$property] = $value;
        return $this;
    }

    /**
     * Get a style property
     */
    public function get(string $property, mixed $default = null): mixed
    {
        return $this->rules[$property] ?? $default;
    }

    /**
     * Check if property exists
     */
    public function has(string $property): bool
    {
        return isset($this->rules[$property]);
    }

    /**
     * Merge with another stylesheet (like CSS cascade)
     */
    public function merge(StyleSheet $other): self
    {
        return new self(array_merge($this->rules, $other->rules));
    }

    /**
     * Get all rules
     */
    public function all(): array
    {
        return $this->rules;
    }

    // ==================== Dimension Properties ====================

    public function width(int|string $parentWidth = 100): int
    {
        $value = $this->get('width', '100%');
        return $this->parseSize($value, $parentWidth);
    }

    public function height(int|string $parentHeight = 30): int
    {
        $value = $this->get('height', '100%');
        return $this->parseSize($value, $parentHeight);
    }

    /**
     * Parse size value (supports px, %, auto)
     */
    private function parseSize(int|string $value, int $parentSize): int
    {
        if (is_int($value)) {
            return $value;
        }

        // Percentage
        if (str_ends_with($value, '%')) {
            $percent = (float) rtrim($value, '%');
            return (int) ($parentSize * ($percent / 100));
        }

        // Pixels (explicit)
        if (str_ends_with($value, 'px')) {
            return (int) rtrim($value, 'px');
        }

        // Auto or other
        return $parentSize;
    }

    // ==================== Position Properties ====================

    public function top(): int
    {
        return (int) $this->get('top', 0);
    }

    public function left(): int
    {
        return (int) $this->get('left', 0);
    }

    public function right(): ?int
    {
        return $this->has('right') ? (int) $this->get('right') : null;
    }

    public function bottom(): ?int
    {
        return $this->has('bottom') ? (int) $this->get('bottom') : null;
    }

    public function position(): string
    {
        return $this->get('position', 'relative');
    }

    // ==================== Spacing Properties ====================

    public function padding(): int|array
    {
        $value = $this->get('padding', 0);

        if (is_int($value)) {
            return ['top' => $value, 'right' => $value, 'bottom' => $value, 'left' => $value];
        }

        // Support "top right bottom left" syntax
        if (is_string($value)) {
            $parts = explode(' ', $value);
            return match(count($parts)) {
                1 => ['top' => (int)$parts[0], 'right' => (int)$parts[0], 'bottom' => (int)$parts[0], 'left' => (int)$parts[0]],
                2 => ['top' => (int)$parts[0], 'right' => (int)$parts[1], 'bottom' => (int)$parts[0], 'left' => (int)$parts[1]],
                3 => ['top' => (int)$parts[0], 'right' => (int)$parts[1], 'bottom' => (int)$parts[2], 'left' => (int)$parts[1]],
                4 => ['top' => (int)$parts[0], 'right' => (int)$parts[1], 'bottom' => (int)$parts[2], 'left' => (int)$parts[3]],
                default => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
            };
        }

        return (array) $value;
    }

    public function margin(): int|array
    {
        $value = $this->get('margin', 0);

        if (is_int($value)) {
            return ['top' => $value, 'right' => $value, 'bottom' => $value, 'left' => $value];
        }

        if (is_string($value)) {
            $parts = explode(' ', $value);
            return match(count($parts)) {
                1 => ['top' => (int)$parts[0], 'right' => (int)$parts[0], 'bottom' => (int)$parts[0], 'left' => (int)$parts[0]],
                2 => ['top' => (int)$parts[0], 'right' => (int)$parts[1], 'bottom' => (int)$parts[0], 'left' => (int)$parts[1]],
                3 => ['top' => (int)$parts[0], 'right' => (int)$parts[1], 'bottom' => (int)$parts[2], 'left' => (int)$parts[1]],
                4 => ['top' => (int)$parts[0], 'right' => (int)$parts[1], 'bottom' => (int)$parts[2], 'left' => (int)$parts[3]],
                default => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
            };
        }

        return (array) $value;
    }

    // ==================== Border Properties ====================

    public function border(): string
    {
        return $this->get('border', 'single');
    }

    public function borderColor(): Color
    {
        return $this->get('border-color', Color::WHITE);
    }

    public function borderStyle(): string
    {
        return $this->get('border-style', 'solid');
    }

    // ==================== Color Properties ====================

    public function background(): Color
    {
        return $this->get('background', Color::BLACK);
    }

    public function foreground(): Color
    {
        return $this->get('foreground', Color::WHITE);
    }

    public function color(): Color
    {
        return $this->foreground();
    }

    // ==================== Text Properties ====================

    public function fontWeight(): string
    {
        return $this->get('font-weight', 'normal');
    }

    public function textAlign(): string
    {
        return $this->get('text-align', 'left');
    }

    public function textDecoration(): string
    {
        return $this->get('text-decoration', 'none');
    }

    // ==================== Display Properties ====================

    public function display(): string
    {
        return $this->get('display', 'block');
    }

    public function visibility(): string
    {
        return $this->get('visibility', 'visible');
    }

    public function zIndex(): int
    {
        return (int) $this->get('z-index', 0);
    }

    // ==================== Helper Methods ====================

    /**
     * Apply ANSI formatting based on style
     */
    public function applyFormatting(string $text): string
    {
        $output = '';

        // Background color
        $output .= $this->background()->toBackgroundCode();

        // Foreground color
        $output .= $this->foreground()->toForegroundCode();

        // Font weight
        if ($this->fontWeight() === 'bold') {
            $output .= "\033[1m";
        }

        // Text decoration
        if ($this->textDecoration() === 'underline') {
            $output .= "\033[4m";
        }

        $output .= $text;
        $output .= "\033[0m"; // Reset

        return $output;
    }

    /**
     * Convert to string for debugging
     */
    public function __toString(): string
    {
        $rules = [];
        foreach ($this->rules as $property => $value) {
            $rules[] = "$property: $value";
        }
        return '{ ' . implode('; ', $rules) . ' }';
    }
}
