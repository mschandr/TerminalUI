<?php

/**
 * Simple TUI Test
 *
 * Basic test to verify the framework works
 */

require_once __DIR__ . '/../vendor/autoload.php';

use TerminalUI\Core\{Application, Window, Rect};
use TerminalUI\Components\{Label, Button};
use TerminalUI\Styling\{StyleSheet, Color};
use TerminalUI\Layout\Border;

// Create application
$app = new Application();

// Create simple window
$window = new Window(
    Rect::fromPoints(10, 5, 70, 15),
    "TUI Framework Test"
);

$window->setBorder(Border::double());
$window->setBorderColor(Color::CYAN);

// Add title label
$titleStyle = StyleSheet::create([
    'top' => 1,
    'left' => 2,
    'foreground' => Color::BRIGHT_YELLOW,
    'font-weight' => 'bold',
]);

$title = new Label("Hello from TerminalUI!", $titleStyle);
$window->add($title);

// Add message label
$messageStyle = StyleSheet::create([
    'top' => 3,
    'left' => 2,
    'foreground' => Color::WHITE,
]);

$message = new Label("Press ESC to exit", $messageStyle);
$window->add($message);

// Add button
$buttonStyle = StyleSheet::create([
    'top' => 5,
    'left' => 2,
    'background' => Color::BLUE,
    'foreground' => Color::WHITE,
    'padding' => 1,
]);

$button = new Button("Click Me!", $buttonStyle, function() use ($window) {
    // Change window title on click
    $window->setTitle("Button Clicked!");
});

$window->add($button);

// Add window to application
$app->addWindow($window);

// Run
$app->run();

echo "\n\nTUI Framework test completed!\n\n";
