<?php
/**
 * Create Sample Posts for Broken Grid Layout
 *
 * Generates test posts to showcase the 3-column broken grid archive.
 *
 * Usage: php create-sample-posts.php
 */

require_once __DIR__ . '/wp-load.php';

echo "Creating sample posts for broken grid layout...\n\n";

// Sample post data
$sample_posts = array(
    array(
        'title'   => '2025 Porsche 911 GT3 RS: The Ultimate Track Weapon',
        'content' => 'The new GT3 RS pushes the boundaries of what a road-legal race car can be. With 518 horsepower and aerodynamics that would make a fighter jet jealous, this is automotive brutalism at its finest.',
    ),
    array(
        'title'   => 'Lamborghini Revuelto: V12 Hybrid Insanity',
        'content' => 'Lamborghini\'s first plug-in hybrid supercar combines a screaming V12 with electric motors for 1,001 horsepower. It\'s excessive, loud, and absolutely glorious.',
    ),
    array(
        'title'   => 'Alfa Romeo 33 Stradale: Italian Art on Wheels',
        'content' => 'Only 33 examples will be made of this stunning coach-built masterpiece. Each one is a rolling sculpture that happens to have a Ferrari-derived V6.',
    ),
    array(
        'title'   => 'BMW M2: The Last Great Driver\'s Car?',
        'content' => 'In a world of electric SUVs, the M2 stands defiant. Rear-wheel drive, manual transmission option, and a twin-turbo inline-six that howls. This might be the last of its kind.',
    ),
    array(
        'title'   => 'Mercedes-AMG GT 63 S E Performance: 843 HP Sedan',
        'content' => 'Four doors, four seats, and enough power to embarrass supercars. This plug-in hybrid sedan is what happens when AMG engineers have unlimited budget.',
    ),
    array(
        'title'   => 'Mazda MX-5 Miata: Joy in Its Purest Form',
        'content' => 'Not the fastest, not the most powerful, but possibly the most fun. The Miata proves you don\'t need 1,000 horsepower to have a smile-inducing driving experience.',
    ),
    array(
        'title'   => 'Ford Mustang Dark Horse: American Muscle Evolved',
        'content' => 'The Dark Horse is Ford\'s answer to track-focused performance. With 500 horsepower and track-tuned suspension, it\'s ready to take on European rivals.',
    ),
    array(
        'title'   => 'Aston Martin DB12: Brutalist Elegance',
        'content' => 'The DB12 ditches the AMG V12 for a twin-turbo V8, but gains agility and modern tech. It\'s still drop-dead gorgeous, but now it can actually corner.',
    ),
    array(
        'title'   => 'Honda Civic Type R: The People\'s Supercar',
        'content' => 'For the price of a loaded BMW 3-Series, you get a car that\'s faster around most tracks. The Type R is proof that horsepower isn\'t everything.',
    ),
);

// Delete existing posts (except pages and media)
$existing_posts = get_posts(array(
    'post_type'      => 'post',
    'posts_per_page' => -1,
    'post_status'    => 'any',
));

foreach ($existing_posts as $post) {
    wp_delete_post($post->ID, true);
    echo "🗑️  Deleted old post: {$post->post_title}\n";
}

echo "\n";

// Create new sample posts
foreach ($sample_posts as $index => $post_data) {
    $post_id = wp_insert_post(array(
        'post_title'   => $post_data['title'],
        'post_content' => $post_data['content'],
        'post_status'  => 'publish',
        'post_author'  => 1,
        'post_date'    => date('Y-m-d H:i:s', strtotime("-{$index} days")),
    ));

    if ($post_id) {
        echo "✅ Created: {$post_data['title']}\n";
    } else {
        echo "❌ Failed to create: {$post_data['title']}\n";
    }
}

echo "\n✅ Done! Visit http://localhost:8881/ to see the broken grid layout.\n";
echo "   You should now see 9 posts in a 3-column grid with offset rhythm.\n";
