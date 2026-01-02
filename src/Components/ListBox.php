<?php

namespace TerminalUI\Components;

use TerminalUI\Core\{View, Rect};
use TerminalUI\Styling\{StyleSheet, Color};
use TerminalUI\Events\{Event, KeyEvent};

/**
 * ListBox Component
 *
 * Scrollable list of items with selection
 *
 * Features:
 * - Up/down arrow navigation
 * - Selection highlighting
 * - Scrolling when list exceeds height
 * - Item activation (Enter key)
 * - Multi-select (future)
 */
class ListBox extends View
{
    protected array $items = [];
    protected int $selectedIndex = 0;
    protected int $scrollOffset = 0;

    protected ?\Closure $onSelect = null;
    protected ?\Closure $onActivate = null;

    public function __construct(
        array $items,
        Rect|StyleSheet $boundsOrStyle,
        ?string $name = null
    ) {
        parent::__construct($boundsOrStyle, $name);
        $this->items = array_values($items); // Re-index
    }

    /**
     * Draw the list
     */
    public function draw(): void
    {
        if (!$this->visible) {
            return;
        }

        $pos = $this->getAbsolutePosition();
        $visibleHeight = $this->bounds->height;

        // Adjust scroll offset if needed
        $this->adjustScrollOffset();

        // Get colors
        $normalBg = $this->style->has('background')
            ? $this->style->get('background')
            : Color::BLACK;
        $normalFg = $this->style->has('foreground')
            ? $this->style->get('foreground')
            : Color::WHITE;
        $selectedBg = $this->style->has('selected-background')
            ? $this->style->get('selected-background')
            : Color::BLUE;
        $selectedFg = $this->style->has('selected-foreground')
            ? $this->style->get('selected-foreground')
            : Color::BRIGHT_WHITE;

        $output = '';

        // Draw visible items
        for ($i = 0; $i < $visibleHeight; $i++) {
            $itemIndex = $this->scrollOffset + $i;

            $output .= $this->moveCursor($pos['x'], $pos['y'] + $i);

            // Draw item or empty space
            if ($itemIndex < count($this->items)) {
                $item = $this->items[$itemIndex];
                $isSelected = ($itemIndex === $this->selectedIndex && $this->focused);

                // Apply colors
                if ($isSelected) {
                    $output .= $selectedBg->toBackgroundCode();
                    $output .= $selectedFg->toForegroundCode();
                    $output .= "\033[1m"; // Bold
                } else {
                    $output .= $normalBg->toBackgroundCode();
                    $output .= $normalFg->toForegroundCode();
                }

                // Render item text (truncate if too long)
                $itemText = $this->truncate($item, $this->bounds->width);
                $output .= str_pad($itemText, $this->bounds->width);

                $output .= "\033[0m";
            } else {
                // Empty row
                $output .= $normalBg->toBackgroundCode();
                $output .= str_repeat(' ', $this->bounds->width);
                $output .= "\033[0m";
            }
        }

        echo $output;

        // Draw children
        $this->drawChildren();
    }

    /**
     * Adjust scroll offset to keep selected item visible
     */
    protected function adjustScrollOffset(): void
    {
        $visibleHeight = $this->bounds->height;

        // Scroll down if selected is below visible area
        if ($this->selectedIndex >= $this->scrollOffset + $visibleHeight) {
            $this->scrollOffset = $this->selectedIndex - $visibleHeight + 1;
        }

        // Scroll up if selected is above visible area
        if ($this->selectedIndex < $this->scrollOffset) {
            $this->scrollOffset = $this->selectedIndex;
        }
    }

    /**
     * Truncate text to fit width
     */
    protected function truncate(string $text, int $width): string
    {
        if (mb_strlen($text) <= $width) {
            return $text;
        }

        return mb_substr($text, 0, $width - 3) . '...';
    }

    /**
     * Handle events
     */
    public function handleEvent(Event $event): bool
    {
        if (!$this->enabled || !$this->visible) {
            return false;
        }

        if ($event instanceof KeyEvent && $this->focused) {
            return match($event->key) {
                KeyEvent::ARROW_UP => $this->selectPrevious(),
                KeyEvent::ARROW_DOWN => $this->selectNext(),
                KeyEvent::HOME => $this->selectFirst(),
                KeyEvent::END => $this->selectLast(),
                KeyEvent::PAGE_UP => $this->pageUp(),
                KeyEvent::PAGE_DOWN => $this->pageDown(),
                KeyEvent::ENTER => $this->activateSelected(),
                default => parent::handleEvent($event)
            };
        }

        return parent::handleEvent($event);
    }

    /**
     * Select previous item
     */
    public function selectPrevious(): bool
    {
        if ($this->selectedIndex > 0) {
            $this->selectedIndex--;
            $this->triggerSelect();
            $this->invalidate();
            return true;
        }

        return false;
    }

    /**
     * Select next item
     */
    public function selectNext(): bool
    {
        if ($this->selectedIndex < count($this->items) - 1) {
            $this->selectedIndex++;
            $this->triggerSelect();
            $this->invalidate();
            return true;
        }

        return false;
    }

    /**
     * Select first item
     */
    public function selectFirst(): bool
    {
        if ($this->selectedIndex !== 0) {
            $this->selectedIndex = 0;
            $this->scrollOffset = 0;
            $this->triggerSelect();
            $this->invalidate();
            return true;
        }

        return false;
    }

    /**
     * Select last item
     */
    public function selectLast(): bool
    {
        $lastIndex = count($this->items) - 1;

        if ($this->selectedIndex !== $lastIndex) {
            $this->selectedIndex = $lastIndex;
            $this->triggerSelect();
            $this->invalidate();
            return true;
        }

        return false;
    }

    /**
     * Page up
     */
    public function pageUp(): bool
    {
        $pageSize = $this->bounds->height;
        $this->selectedIndex = max(0, $this->selectedIndex - $pageSize);
        $this->triggerSelect();
        $this->invalidate();
        return true;
    }

    /**
     * Page down
     */
    public function pageDown(): bool
    {
        $pageSize = $this->bounds->height;
        $this->selectedIndex = min(
            count($this->items) - 1,
            $this->selectedIndex + $pageSize
        );
        $this->triggerSelect();
        $this->invalidate();
        return true;
    }

    /**
     * Activate selected item
     */
    public function activateSelected(): bool
    {
        if ($this->onActivate && isset($this->items[$this->selectedIndex])) {
            ($this->onActivate)($this->items[$this->selectedIndex], $this->selectedIndex);
            return true;
        }

        return false;
    }

    /**
     * Trigger select callback
     */
    protected function triggerSelect(): void
    {
        if ($this->onSelect && isset($this->items[$this->selectedIndex])) {
            ($this->onSelect)($this->items[$this->selectedIndex], $this->selectedIndex);
        }
    }

    // ==================== Configuration ====================

    /**
     * Set items
     */
    public function setItems(array $items): self
    {
        $this->items = array_values($items);
        $this->selectedIndex = 0;
        $this->scrollOffset = 0;
        $this->invalidate();
        return $this;
    }

    /**
     * Get items
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Add item
     */
    public function addItem(string $item): self
    {
        $this->items[] = $item;
        $this->invalidate();
        return $this;
    }

    /**
     * Remove item
     */
    public function removeItem(int $index): self
    {
        if (isset($this->items[$index])) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);

            // Adjust selected index if needed
            if ($this->selectedIndex >= count($this->items)) {
                $this->selectedIndex = max(0, count($this->items) - 1);
            }

            $this->invalidate();
        }

        return $this;
    }

    /**
     * Get selected item
     */
    public function getSelectedItem(): ?string
    {
        return $this->items[$this->selectedIndex] ?? null;
    }

    /**
     * Get selected index
     */
    public function getSelectedIndex(): int
    {
        return $this->selectedIndex;
    }

    /**
     * Set selected index
     */
    public function setSelectedIndex(int $index): self
    {
        if ($index >= 0 && $index < count($this->items)) {
            $this->selectedIndex = $index;
            $this->triggerSelect();
            $this->invalidate();
        }

        return $this;
    }

    /**
     * Set selection callback
     */
    public function setOnSelect(\Closure $callback): self
    {
        $this->onSelect = $callback;
        return $this;
    }

    /**
     * Set activation callback (Enter key)
     */
    public function setOnActivate(\Closure $callback): self
    {
        $this->onActivate = $callback;
        return $this;
    }
}
