<?php

namespace TerminalUI\Events;

/**
 * Mouse Event
 *
 * Represents mouse clicks and movement
 *
 * Future enhancement - basic structure for now
 */
class MouseEvent extends Event
{
    public const LEFT_CLICK = 1;
    public const RIGHT_CLICK = 2;
    public const MIDDLE_CLICK = 3;
    public const SCROLL_UP = 4;
    public const SCROLL_DOWN = 5;

    public readonly int $x;
    public readonly int $y;
    public readonly int $button;
    public readonly bool $pressed; // true = press, false = release

    public function __construct(int $x, int $y, int $button, bool $pressed = true)
    {
        $this->x = $x;
        $this->y = $y;
        $this->button = $button;
        $this->pressed = $pressed;
    }

    /**
     * Check if this is a left click
     */
    public function isLeftClick(): bool
    {
        return $this->button === self::LEFT_CLICK && $this->pressed;
    }

    /**
     * Check if this is a right click
     */
    public function isRightClick(): bool
    {
        return $this->button === self::RIGHT_CLICK && $this->pressed;
    }

    /**
     * Get position as array
     */
    public function getPosition(): array
    {
        return ['x' => $this->x, 'y' => $this->y];
    }
}
