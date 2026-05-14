<?php
/**
 * includes/functions.php
 *
 * Pure-function helpers for data fetching and output safety.
 * No side-effects — no output, no session writes.
 */

declare(strict_types=1);

/**
 * Fetches all portfolio projects, optionally filtered by category.
 *
 * @param  PDO         $db       Shared PDO instance from getDB()
 * @param  string      $lang     Active language ('th' | 'en') — selects title/desc columns
 * @param  string|null $category ENUM value to filter, or null for all projects
 * @return array<int, array<string, mixed>>
 */
function getProjects(PDO $db, string $lang, ?string $category = null): array
{
    // Build query dynamically but safely: the WHERE clause only changes when
    // $category is set; the parameter binding prevents injection either way.
    $sql = 'SELECT
                id,
                title_th,
                title_en,
                category,
                description_th,
                description_en,
                image_path,
                github_link,
                is_featured,
                sort_order
            FROM portfolio_projects';

    $params = [];

    if ($category !== null) {
        $sql     .= ' WHERE category = :category';
        $params[':category'] = $category;
    }

    $sql .= ' ORDER BY is_featured DESC, sort_order ASC, id ASC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();   // PDO::FETCH_ASSOC is default from config

    // Attach a convenience "title" and "description" key in the active language
    // so templates don't need inline ternary every time.
    foreach ($rows as &$row) {
        $row['title']       = $lang === 'th' ? $row['title_th']       : $row['title_en'];
        $row['description'] = $lang === 'th' ? $row['description_th'] : $row['description_en'];
    }
    unset($row);

    return $rows;
}

/**
 * Returns a list of distinct categories that have at least one project.
 * Used to build the filter bar.
 *
 * @return array<int, string>  e.g. ['web', 'database', 'hardware']
 */
function getActiveCategories(PDO $db): array
{
    $stmt = $db->query(
        'SELECT DISTINCT category FROM portfolio_projects ORDER BY category ASC'
    );
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Escapes a string for safe HTML output (prevents XSS).
 * Always call this when outputting user-supplied or DB-sourced data.
 */
function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Assigns a Bento-Box CSS class based on the card's position in the list.
 * The pattern creates an asymmetric, visually interesting grid layout.
 *
 *  Index 0 → bento-featured  (3 cols × 2 rows) — always the featured project
 *  Index 1 → bento-wide      (3 cols × 1 row)
 *  Index 2 → bento-medium    (2 cols × 1 row)
 *  Index 3 → bento-small     (1 col  × 1 row)
 *  Index 4+→ alternates between medium and small
 */
function bentoClass(int $index, bool $isFeatured): string
{
    if ($isFeatured && $index === 0) {
        return 'bento-featured';
    }

    $pattern = ['bento-wide', 'bento-medium', 'bento-small', 'bento-medium'];
    return $pattern[$index % count($pattern)];
}

/**
 * Returns a CSS accent-color class name per category for badge coloring.
 */
function categoryAccentClass(string $category): string
{
    return match ($category) {
        'web'        => 'accent-blue',
        'database'   => 'accent-cyan',
        'hardware'   => 'accent-purple',
        'it_support' => 'accent-green',
        default      => 'accent-blue',
    };
}

/**
 * Validates that a URL is an absolute HTTPS URL before outputting it as an href.
 * Prevents javascript: URI injection in github_link / image_path fields.
 */
function safeUrl(?string $url): string
{
    if ($url === null || $url === '') {
        return '#';
    }
    $parsed = parse_url($url);
    if (
        isset($parsed['scheme']) &&
        in_array(strtolower($parsed['scheme']), ['https', 'http'], true)
    ) {
        return h($url);
    }
    return '#';
}

/**
 * Returns tech-stack tag strings for the card footer, keyed by category.
 * @return array<string>
 */
function categoryTechTags(string $category): array
{
    return match ($category) {
        'web'        => ['PHP', 'MySQL', 'JavaScript', 'Docker'],
        'database'   => ['MySQL', 'MariaDB', 'SQL', 'Triggers'],
        'hardware'   => ['Assembly', 'Diagnostics', 'Repair'],
        'it_support' => ['Active Directory', 'Networking', 'Linux'],
        default      => [],
    };
}

/**
 * Returns true when a URL points to GitHub — used to pick the right button label.
 */
function isGithubUrl(?string $url): bool
{
    return $url !== null && stripos($url, 'github.com') !== false;
}

/**
 * Returns a relative image src, or the placeholder path if the image is missing.
 */
function projectImage(?string $imagePath): string
{
    if ($imagePath && file_exists(__DIR__ . '/../' . $imagePath)) {
        return h($imagePath);
    }
    return 'assets/images/placeholder.svg';
}
