<?php

declare(strict_types=1);

namespace Millancore\Pesto\Benchmarks;

final class DataProvider
{
    public static function simple(): array
    {
        return [
            'title' => 'Benchmark Page',
            'heading' => 'Hello World',
            'content' => 'This is a <strong>benchmark</strong> test with special chars: &, <, >, "quotes".',
            'author' => 'John Doe',
        ];
    }

    public static function loop(): array
    {
        $items = [];
        for ($i = 0; $i < 100; $i++) {
            $items[] = [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'bio' => "Bio for user {$i} with <html> entities & \"quotes\".",
            ];
        }

        return [
            'title' => 'Users List',
            'heading' => 'All Users',
            'items' => $items,
        ];
    }

    public static function conditional(): array
    {
        $notifications = [];
        for ($i = 0; $i < 50; $i++) {
            $notifications[] = [
                'message' => "Notification #{$i}: Something happened.",
                'unread' => $i % 3 === 0,
            ];
        }

        return [
            'title' => 'Dashboard',
            'name' => 'Jane Doe',
            'role' => 'editor',
            'notifications' => $notifications,
        ];
    }

    public static function partial(): array
    {
        return [
            'title' => 'Page with Partial',
            'siteName' => 'My Website',
            'heading' => 'Main Content',
            'content' => 'This page includes a header partial.',
        ];
    }
}
