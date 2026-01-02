<?php

namespace TerminalUI\Core;

use TerminalUI\Styling\StyleSheet;
use TerminalUI\Events\Event;

/**
 * Base View Class
 *
 * All UI components inherit from this (like TView in Turbo Vision)
 *
 * Hierarchy:
 * - View (base)
 *   - Window (has border, title)
 *     - Dialog (modal window)
 *   - Panel (container)
 *   - Button, Label, ListBox, etc. (leaf components)
 */
abstract class View
{
    protected Rect $bounds;
    protected StyleSheet $style;
    protected ?View $parent = null;
    protected array $children = [];

    protected bool $visible = true;
    protected bool $enabled = true;
    protected bool $focused = false;

    protected string $name = '';

    public function __construct(Rect|StyleSheet $boundsOrStyle, ?string $name = null)
    {
        if ($boundsOrStyle instanceof Rect) {
            $this->bounds = $boundsOrStyle;
            $this->style = StyleSheet::create();
        } else {
            $this->style = $boundsOrStyle;
            $this->bounds = Rect::fromStyle($this->style);
        }

        $this->name = $name ?? static::class;
    }

    // ==================== Core Abstract Methods ====================

    /**
     * Draw this view to the terminal
     */
    abstract public function draw(): void;

    /**
     * Handle an event (keyboard, mouse, etc.)
     *
     * @return bool True if event was handled
     */
    public function handleEvent(Event $event): bool
    {
        // Pass to focused child first
        foreach ($this->children as $child) {
            if ($child->focused && $child->handleEvent($event)) {
                return true;
            }
        }

        // Not handled by children
        return false;
    }

    // ==================== Child Management ====================

    /**
     * Add a child view
     */
    public function add(View $child): self
    {
        $child->parent = $this;
        $this->children[] = $child;
        return $this;
    }

    /**
     * Remove a child view
     */
    public function remove(View $child): self
    {
        $this->children = array_filter(
            $this->children,
            fn($c) => $c !== $child
        );
        $child->parent = null;
        return $this;
    }

    /**
     * Remove all children
     */
    public function removeAll(): self
    {
        foreach ($this->children as $child) {
            $child->parent = null;
        }
        $this->children = [];
        return $this;
    }

    /**
     * Draw all children
     */
    protected function drawChildren(): void
    {
        foreach ($this->children as $child) {
            if ($child->visible) {
                $child->draw();
            }
        }
    }

    /**
     * Get all children
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    // ==================== Focus Management ====================

    /**
     * Give focus to this view
     */
    public function focus(): void
    {
        if ($this->parent) {
            // Tell parent to focus us
            $this->parent->setFocusedChild($this);
        }
        $this->focused = true;
        $this->onFocus();
    }

    /**
     * Remove focus from this view
     */
    public function blur(): void
    {
        $this->focused = false;
        $this->onBlur();
    }

    /**
     * Set which child has focus
     */
    protected function setFocusedChild(View $child): void
    {
        // Blur all other children
        foreach ($this->children as $c) {
            if ($c !== $child) {
                $c->blur();
            }
        }

        $child->focus();
    }

    /**
     * Focus next sibling (tab key)
     */
    public function focusNext(): bool
    {
        if (!$this->parent) {
            return false;
        }

        $siblings = $this->parent->children;
        $currentIndex = array_search($this, $siblings, true);

        if ($currentIndex === false) {
            return false;
        }

        // Find next focusable sibling
        $count = count($siblings);
        for ($i = 1; $i < $count; $i++) {
            $nextIndex = ($currentIndex + $i) % $count;
            $next = $siblings[$nextIndex];

            if ($next->enabled && $next->visible) {
                $next->focus();
                return true;
            }
        }

        return false;
    }

    /**
     * Focus previous sibling (shift+tab)
     */
    public function focusPrev(): bool
    {
        if (!$this->parent) {
            return false;
        }

        $siblings = $this->parent->children;
        $currentIndex = array_search($this, $siblings, true);

        if ($currentIndex === false) {
            return false;
        }

        $count = count($siblings);
        for ($i = 1; $i < $count; $i++) {
            $prevIndex = ($currentIndex - $i + $count) % $count;
            $prev = $siblings[$prevIndex];

            if ($prev->enabled && $prev->visible) {
                $prev->focus();
                return true;
            }
        }

        return false;
    }

    // ==================== Event Hooks (Override in Subclasses) ====================

    protected function onFocus(): void {}
    protected function onBlur(): void {}
    protected function onClick(): void {}
    protected function onKeyPress(string $key): void {}

    // ==================== Visibility & State ====================

    public function show(): self
    {
        $this->visible = true;
        return $this;
    }

    public function hide(): self
    {
        $this->visible = false;
        return $this;
    }

    public function enable(): self
    {
        $this->enabled = true;
        return $this;
    }

    public function disable(): self
    {
        $this->enabled = false;
        return $this;
    }

    public function isVisible(): bool { return $this->visible; }
    public function isEnabled(): bool { return $this->enabled; }
    public function isFocused(): bool { return $this->focused; }

    // ==================== Bounds & Positioning ====================

    public function getBounds(): Rect
    {
        return $this->bounds;
    }

    public function setBounds(Rect $bounds): self
    {
        $this->bounds = $bounds;
        return $this;
    }

    /**
     * Get absolute screen position
     */
    public function getAbsolutePosition(): array
    {
        $x = $this->bounds->x;
        $y = $this->bounds->y;

        if ($this->parent) {
            $parentPos = $this->parent->getAbsolutePosition();
            $x += $parentPos['x'];
            $y += $parentPos['y'];
        }

        return ['x' => $x, 'y' => $y];
    }

    /**
     * Move cursor to absolute position
     */
    protected function moveCursor(int $x, int $y): string
    {
        return "\033[{$y};{$x}H";
    }

    /**
     * Clear this view's area
     */
    protected function clear(): string
    {
        $output = '';
        $pos = $this->getAbsolutePosition();

        for ($y = 0; $y < $this->bounds->height; $y++) {
            $output .= $this->moveCursor($pos['x'], $pos['y'] + $y);
            $output .= str_repeat(' ', $this->bounds->width);
        }

        return $output;
    }

    // ==================== Style Management ====================

    public function getStyle(): StyleSheet
    {
        return $this->style;
    }

    public function setStyle(StyleSheet $style): self
    {
        $this->style = $style;
        return $this;
    }

    // ==================== Utility ====================

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getParent(): ?View
    {
        return $this->parent;
    }

    /**
     * Find child by name
     */
    public function findChild(string $name): ?View
    {
        foreach ($this->children as $child) {
            if ($child->name === $name) {
                return $child;
            }

            // Recursive search
            $found = $child->findChild($name);
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    /**
     * Get root application
     */
    public function getApplication(): ?Application
    {
        $root = $this;
        while ($root->parent) {
            $root = $root->parent;
        }

        return $root instanceof Application ? $root : null;
    }

    /**
     * Request redraw
     */
    public function invalidate(): void
    {
        $app = $this->getApplication();
        if ($app) {
            $app->requestRedraw();
        }
    }
}
