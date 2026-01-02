# Integrating TerminalUI with Space Wars 3002

## Installation

### Option 1: Local Development (Recommended during development)

```bash
cd /home/mdhas/workspace/space-wars-3002

# Add local package to composer.json
composer config repositories.terminalui path ../terminalui-framework

# Require the package
composer require terminalui/framework:@dev
```

### Option 2: Publish to Packagist (For production)

```bash
cd /home/mdhas/workspace/terminalui-framework
git init
git add .
git commit -m "Initial commit: TerminalUI Framework"

# Push to GitHub and publish on packagist.org
# Then in Space Wars:
composer require terminalui/framework
```

## Migration Guide: Converting Space Wars UI

### Before (Old System)

```php
// app/Console/Commands/PlayerInterfaceCommand.php

private function showGalaxyMap(): void
{
    $this->clearScreen();
    $this->line(str_repeat('═', 100));
    $this->line('  GALAXY MAP');
    $this->line(str_repeat('═', 100));

    // Lots of manual ANSI codes...
    echo "\033[31mRed text\033[0m\n";
    // ...
}
```

### After (With TerminalUI)

```php
// app/Console/Tui/Windows/GalaxyMapWindow.php

use TerminalUI\Core\{Window, Rect};
use TerminalUI\Components\Label;
use TerminalUI\Styling\{StyleSheet, Color};
use TerminalUI\Layout\Border;

class GalaxyMapWindow extends Window
{
    public function __construct(Player $player, GalaxySectorCache $cache)
    {
        // Two-point definition!
        parent::__construct(
            Rect::fromPoints(5, 2, 115, 28),
            "Galaxy Map - Sector View"
        );

        $this->setBorder(Border::double());

        // CSS-like styled title
        $titleStyle = StyleSheet::create([
            'top' => 1,
            'left' => 2,
            'font-weight' => 'bold',
            'foreground' => Color::BRIGHT_CYAN,
        ]);

        $this->add(new Label(
            "Sector: {$this->getSectorName($player)}",
            $titleStyle
        ));

        // Cached sector rendering
        $this->renderSector($player, $cache);
    }

    private function renderSector(Player $player, GalaxySectorCache $cache): void
    {
        $sector = $this->getSector($player);
        $ascii = $cache->get($player->galaxy_id, $sector['x'], $sector['y']);

        // Render cached ASCII at specific position
        $this->addContent($ascii, 3, 2);

        // Overlay player position (blinking red dot)
        $this->overlayPlayerDot($player);
    }
}
```

## Example: Complete Galaxy Screen Conversion

### 1. Create Window Class

```php
<?php

namespace App\Console\Tui\Windows;

use App\Models\Player;
use App\Services\GalaxySectorCache;
use TerminalUI\Core\{Window, Rect};
use TerminalUI\Components\{Label, ListBox, Panel};
use TerminalUI\Styling\{StyleSheet, Color};
use TerminalUI\Layout\Border;
use TerminalUI\Events\KeyEvent;

class GalaxyMapWindow extends Window
{
    private Player $player;
    private GalaxySectorCache $sectorCache;

    public function __construct(Player $player, GalaxySectorCache $cache)
    {
        $this->player = $player;
        $this->sectorCache = $cache;

        // Full-screen window with two-point definition
        parent::__construct(
            Rect::fromPoints(0, 0, 120, 30),
            "Galaxy Map"
        );

        $this->setBorder(Border::double());
        $this->buildUI();
    }

    private function buildUI(): void
    {
        // Header panel (100% width, 3 lines tall)
        $headerStyle = StyleSheet::create([
            'top' => 0,
            'left' => 0,
            'width' => '100%',
            'height' => 3,
            'background' => Color::BLUE,
            'foreground' => Color::WHITE,
        ]);

        $header = new Panel($headerStyle);
        $header->add(new Label(
            $this->getPlayerStats(),
            StyleSheet::create([
                'top' => 1,
                'left' => 2,
                'font-weight' => 'bold',
            ])
        ));

        $this->add($header);

        // Main sector view (70% width, left side)
        $this->addSectorView();

        // Side panel (30% width, right side)
        $this->addSidePanel();

        // Status bar (bottom)
        $this->addStatusBar();
    }

    private function addSectorView(): void
    {
        $style = StyleSheet::create([
            'top' => 3,
            'left' => 0,
            'width' => '70%',
            'height' => 24,
            'border' => 'single',
            'border-color' => Color::GREEN,
        ]);

        $sectorPanel = new Panel($style);

        // Get cached sector ASCII
        $sector = $this->getCurrentSector();
        $ascii = $this->sectorCache->get(
            $this->player->galaxy_id,
            $sector['x'],
            $sector['y']
        );

        $sectorPanel->addText($ascii);
        $this->add($sectorPanel);
    }

    private function addSidePanel(): void
    {
        $style = StyleSheet::create([
            'top' => 3,
            'right' => 0,     // Stick to right!
            'width' => '30%',
            'height' => 24,
            'border' => 'rounded',
            'border-color' => Color::CYAN,
        ]);

        $sidePanel = new Panel($style);

        // Title
        $sidePanel->add(new Label(
            "Available Gates",
            StyleSheet::create([
                'top' => 1,
                'font-weight' => 'bold',
                'foreground' => Color::BRIGHT_YELLOW,
            ])
        ));

        // Gate list
        $gates = $this->getAvailableGates();
        $gateList = new ListBox(
            $gates,
            StyleSheet::create([
                'top' => 3,
                'left' => 1,
                'width' => '90%',
                'height' => 15,
            ])
        );

        $sidePanel->add($gateList);
        $this->add($sidePanel);
    }

    private function addStatusBar(): void
    {
        $style = StyleSheet::create([
            'bottom' => 0,
            'left' => 0,
            'width' => '100%',
            'height' => 1,
            'background' => Color::BRIGHT_BLACK,
            'foreground' => Color::WHITE,
        ]);

        $status = new Label(
            "W/A/S/D: Pan | T: Travel | Q: Quit",
            $style
        );

        $this->add($status);
    }

    public function handleKeyPress(KeyEvent $event): bool
    {
        return match($event->key) {
            'w' => $this->panNorth(),
            'a' => $this->panWest(),
            's' => $this->panSouth(),
            'd' => $this->panEast(),
            't' => $this->openTravelMenu(),
            'q' => $this->close(),
            default => false,
        };
    }

    private function getPlayerStats(): string
    {
        return sprintf(
            "Captain %s | Credits: %s | Level: %d | Ship: %s",
            $this->player->call_sign,
            number_format($this->player->credits),
            $this->player->level,
            $this->player->activeShip->ship->name ?? 'None'
        );
    }

    private function getCurrentSector(): array
    {
        return [
            'x' => floor($this->player->currentLocation->x / 100),
            'y' => floor($this->player->currentLocation->y / 100),
        ];
    }
}
```

### 2. Update Main Command

```php
<?php

namespace App\Console\Commands;

use App\Console\Tui\Windows\GalaxyMapWindow;
use App\Services\GalaxySectorCache;
use TerminalUI\Core\Application;

class PlayerInterfaceCommand extends Command
{
    public function handle(): int
    {
        // Create TUI application
        $app = new Application();

        // Create galaxy map window
        $galaxyWindow = new GalaxyMapWindow(
            $this->player,
            app(GalaxySectorCache::class)
        );

        $app->addWindow($galaxyWindow);
        $app->run();

        return 0;
    }
}
```

## Benefits

### Before
- ❌ Manual ANSI codes everywhere
- ❌ Hardcoded positions
- ❌ No reusability
- ❌ Difficult to maintain
- ❌ No separation of concerns

### After
- ✅ CSS-like styling
- ✅ Responsive layouts (percentages!)
- ✅ Reusable components
- ✅ Easy to maintain
- ✅ Clean separation
- ✅ Two-point window definition
- ✅ Custom borders

## Performance

The cached sector system works perfectly with TerminalUI:

```php
// Sector cache returns ASCII string
$ascii = $sectorCache->get($galaxyId, $sectorX, $sectorY);

// TerminalUI renders it efficiently
$panel->addContent($ascii, x: 2, y: 3);

// Overlay dynamic elements (player position, etc.)
$panel->overlayAt($playerX, $playerY, "●", Color::RED);
```

## Next Steps

1. Convert main menu to TerminalUI
2. Convert galaxy map to TerminalUI
3. Convert trading screen to TerminalUI
4. Convert combat screen to TerminalUI
5. Convert colony management to TerminalUI

Each conversion will be cleaner, more maintainable, and more powerful!
