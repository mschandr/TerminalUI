<?php

namespace TerminalUI\Core;

/**
 * Rectangle - Defines position and size of UI elements
 *
 * Supports multiple creation methods:
 * - Rect::make(x, y, width, height)
 * - Rect::fromPoints(x1, y1, x2, y2)  // Two-point definition!
 * - Rect::fromStyle(styleSheet, parentRect)  // CSS-like positioning
 */
class Rect
{
    public function __construct(
        public int $x,
        public int $y,
        public int $width,
        public int $height
    ) {}

    /**
     * Create from x, y, width, height
     */
    public static function make(int $x, int $y, int $width, int $height): self
    {
        return new self($x, $y, $width, $height);
    }

    /**
     * Create from two points (top-left and bottom-right)
     *
     * This is what the user asked for!
     * Define window with two coordinates like CSS:
     *
     * $window = Rect::fromPoints(10, 5, 90, 25);
     * // Creates a rect from (10,5) to (90,25)
     */
    public static function fromPoints(int $x1, int $y1, int $x2, int $y2): self
    {
        return new self(
            x: min($x1, $x2),
            y: min($y1, $y2),
            width: abs($x2 - $x1) + 1,
            height: abs($y2 - $y1) + 1
        );
    }

    /**
     * Create from CSS-like StyleSheet
     *
     * Supports:
     * - width/height (px, %, auto)
     * - top/left (absolute positioning)
     * - right/bottom (calculate from parent)
     * - margin/padding
     */
    public static function fromStyle(\TerminalUI\Styling\StyleSheet $style, ?self $parent = null): self
    {
        $parent = $parent ?? self::make(0, 0, 120, 30); // Default terminal size

        // Calculate position
        $x = $style->left();
        $y = $style->top();

        // If right/bottom are set, calculate size from parent
        if ($style->right() !== null) {
            $width = $parent->width - $x - $style->right();
        } else {
            $width = $style->width($parent->width);
        }

        if ($style->bottom() !== null) {
            $height = $parent->height - $y - $style->bottom();
        } else {
            $height = $style->height($parent->height);
        }

        // Apply margin
        $margin = $style->margin();
        if (is_array($margin)) {
            $x += $margin['left'];
            $y += $margin['top'];
            $width -= ($margin['left'] + $margin['right']);
            $height -= ($margin['top'] + $margin['bottom']);
        }

        return new self($x, $y, $width, $height);
    }

    /**
     * Get content area (after applying padding)
     */
    public function content(\TerminalUI\Styling\StyleSheet $style): self
    {
        $padding = $style->padding();

        if (!is_array($padding)) {
            $padding = ['top' => $padding, 'right' => $padding, 'bottom' => $padding, 'left' => $padding];
        }

        return new self(
            x: $this->x + $padding['left'],
            y: $this->y + $padding['top'],
            width: $this->width - ($padding['left'] + $padding['right']),
            height: $this->height - ($padding['top'] + $padding['bottom'])
        );
    }

    // ==================== Geometry Methods ====================

    public function contains(int $x, int $y): bool
    {
        return $x >= $this->x && $x < $this->x + $this->width &&
               $y >= $this->y && $y < $this->y + $this->height;
    }

    public function intersects(self $other): bool
    {
        return !($this->x + $this->width <= $other->x ||
                 $other->x + $other->width <= $this->x ||
                 $this->y + $this->height <= $other->y ||
                 $other->y + $other->height <= $this->y);
    }

    public function center(): array
    {
        return [
            'x' => $this->x + (int)($this->width / 2),
            'y' => $this->y + (int)($this->height / 2),
        ];
    }

    // ==================== Positioning Helpers ====================

    public function top(): int { return $this->y; }
    public function left(): int { return $this->x; }
    public function right(): int { return $this->x + $this->width - 1; }
    public function bottom(): int { return $this->y + $this->height - 1; }

    /**
     * Create a new rect moved by offset
     */
    public function move(int $dx, int $dy): self
    {
        return new self($this->x + $dx, $this->y + $dy, $this->width, $this->height);
    }

    /**
     * Create a new rect with different size
     */
    public function resize(int $newWidth, int $newHeight): self
    {
        return new self($this->x, $this->y, $newWidth, $newHeight);
    }

    /**
     * Create a new rect shrunk by amount
     */
    public function shrink(int $amount): self
    {
        return new self(
            x: $this->x + $amount,
            y: $this->y + $amount,
            width: max(1, $this->width - ($amount * 2)),
            height: max(1, $this->height - ($amount * 2))
        );
    }

    /**
     * Create a new rect expanded by amount
     */
    public function expand(int $amount): self
    {
        return new self(
            x: $this->x - $amount,
            y: $this->y - $amount,
            width: $this->width + ($amount * 2),
            height: $this->height + ($amount * 2)
        );
    }

    /**
     * Get area (width × height)
     */
    public function area(): int
    {
        return $this->width * $this->height;
    }

    /**
     * Debug output
     */
    public function __toString(): string
    {
        return sprintf(
            'Rect(x=%d, y=%d, w=%d, h=%d)',
            $this->x,
            $this->y,
            $this->width,
            $this->height
        );
    }
}
