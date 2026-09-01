#!/usr/bin/env php
<?php

/**
 * Seed the database with demo content — the same authors, categories,
 * posts, comments, and media that assets/js/mock-data.js has been
 * driving the frontend from. This is Section 12's first step: real rows
 * to switch the frontend onto before mock-data.js is removed.
 *
 * Idempotent by default — if blog_users already has rows, it does
 * nothing (safe to run again after a partial `migrate`). Pass --fresh
 * to wipe all blog_* content tables and reseed from scratch.
 *
 * Usage:
 *   php bin/seed.php
 *   php bin/seed.php --fresh
 */

declare(strict_types=1);

require __DIR__ . '/../core/Env.php';
require __DIR__ . '/../core/Config.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Str.php';

Env::load(__DIR__ . '/../.env');
Config::init(__DIR__ . '/../config');

$fresh = in_array('--fresh', $argv, true);

fwrite(STDOUT, "Connecting to database...\n");

try {
    $pdo = Database::connection();
} catch (Throwable $e) {
    fwrite(STDERR, "Seed failed: " . $e->getMessage() . "\n");
    exit(1);
}

// No whole-script "already seeded" gate: every section below checks its
// own rows (by email/slug/title) before inserting, so a partial prior
// run — e.g. users + categories landed but posts didn't — resumes and
// fills in only what's missing, instead of being skipped wholesale.

if ($fresh) {
    fwrite(STDOUT, "--fresh: wiping existing content tables...\n");
    // Children before parents, respecting FK constraints.
    foreach ([
        'blog_comments',
        'blog_post_tags',
        'blog_post_views_daily',
        'blog_media',
        'blog_posts',
        'blog_categories',
        'blog_tags',
        'blog_users',
    ] as $table) {
        Database::execute("DELETE FROM {$table}");
        Database::execute("ALTER TABLE {$table} AUTO_INCREMENT = 1");
    }
}

// ---------------------------------------------------------------------
// 1. Authors (blog_users) — the four MOCK_AUTHORS as 'author' accounts,
//    plus one dedicated 'admin' account not present in the mock data
//    (the mock prototype never modeled an admin — the dashboard just
//    assumed one), so the admin-only routes have someone real to log in as.
// ---------------------------------------------------------------------

$seedPassword = 'Seed@12345';
$passwordHash = password_hash($seedPassword, PASSWORD_DEFAULT);

$authorSeeds = [
    'a1' => ['name' => 'Sarah Chen',      'email' => 'sarah.chen@skoolyst.com',   'role' => 'author', 'avatar_url' => 'https://i.pravatar.cc/150?img=47',
        'bio' => "Sarah is Skoolyst's lead content strategist. She writes about digital pedagogy, curriculum design, and the future of classroom technology."],
    'a2' => ['name' => 'Marcus Johnson',  'email' => 'marcus.johnson@skoolyst.com', 'role' => 'author', 'avatar_url' => 'https://i.pravatar.cc/150?img=12',
        'bio' => 'Marcus is a former high-school math teacher turned edtech writer. He focuses on STEM engagement and data-driven instruction.'],
    'a3' => ['name' => 'Priya Patel',     'email' => 'priya.patel@skoolyst.com',  'role' => 'author', 'avatar_url' => 'https://i.pravatar.cc/150?img=45',
        'bio' => "Priya covers online learning trends and the student experience. She holds a Master's in Instructional Design."],
    'a4' => ['name' => 'David Kim',       'email' => 'david.kim@skoolyst.com',    'role' => 'author', 'avatar_url' => 'https://i.pravatar.cc/150?img=33',
        'bio' => 'David writes about education policy, funding, and the business of learning. He previously covered K-12 for a national outlet.'],
];
$adminSeed = ['name' => 'Blog Admin', 'email' => 'admin@skoolyst.com', 'role' => 'admin', 'avatar_url' => null, 'bio' => null];

$authorIds = []; // mock key ('a1'..'a4') => real blog_users.id
foreach ($authorSeeds as $key => $u) {
    $row = Database::selectOne('SELECT id FROM blog_users WHERE email = ?', [$u['email']]);
    if ($row) {
        $authorIds[$key] = (int) $row['id'];
        continue;
    }
    Database::execute(
        'INSERT INTO blog_users (name, email, password_hash, role, status, avatar_url, bio) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$u['name'], $u['email'], $passwordHash, $u['role'], 'active', $u['avatar_url'], $u['bio']]
    );
    $authorIds[$key] = (int) Database::lastInsertId();
}

$adminRow = Database::selectOne('SELECT id FROM blog_users WHERE email = ?', [$adminSeed['email']]);
if ($adminRow) {
    $adminId = (int) $adminRow['id'];
} else {
    Database::execute(
        'INSERT INTO blog_users (name, email, password_hash, role, status, avatar_url, bio) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$adminSeed['name'], $adminSeed['email'], $passwordHash, $adminSeed['role'], 'active', $adminSeed['avatar_url'], $adminSeed['bio']]
    );
    $adminId = (int) Database::lastInsertId();
}

fwrite(STDOUT, "Seeded " . (count($authorIds) + 1) . " users (4 authors + 1 admin).\n");

// ---------------------------------------------------------------------
// 2. Categories (blog_categories) — the five MOCK_CATEGORIES, verbatim.
// ---------------------------------------------------------------------

$categorySeeds = [
    'c1' => ['name' => 'Teaching Strategies', 'slug' => 'teaching-strategies', 'description' => 'Practical approaches for the modern classroom.', 'color' => '#0f4077'],
    'c2' => ['name' => 'EdTech',              'slug' => 'edtech',              'description' => "Technology that's reshaping how students learn.", 'color' => '#4361ee'],
    'c3' => ['name' => 'Student Success',     'slug' => 'student-success',     'description' => 'Tips and research on helping every learner thrive.', 'color' => '#0d9488'],
    'c4' => ['name' => 'Online Learning',     'slug' => 'online-learning',     'description' => 'Best practices for virtual and hybrid classrooms.', 'color' => '#7c3aed'],
    'c5' => ['name' => 'Education Policy',    'slug' => 'education-policy',    'description' => 'Funding, standards, and the business of learning.', 'color' => '#d97706'],
];

$categoryIds = [];
foreach ($categorySeeds as $key => $c) {
    $row = Database::selectOne('SELECT id FROM blog_categories WHERE slug = ?', [$c['slug']]);
    if ($row) {
        $categoryIds[$key] = (int) $row['id'];
        continue;
    }
    Database::execute(
        'INSERT INTO blog_categories (name, slug, description, color) VALUES (?, ?, ?, ?)',
        [$c['name'], $c['slug'], $c['description'], $c['color']]
    );
    $categoryIds[$key] = (int) Database::lastInsertId();
}

fwrite(STDOUT, "Seeded " . count($categoryIds) . " categories.\n");

// ---------------------------------------------------------------------
// 3. Posts (blog_posts) — the twelve MOCK_POSTS. Slugs are re-run through
//    Str::slugify() (same helper the API uses) rather than trusted as-is,
//    so they match exactly what the app would generate for this title.
// ---------------------------------------------------------------------

$postSeeds = [
    ['id' => 'p1', 'title' => '5 Active Learning Strategies That Actually Work in 2026', 'category' => 'c1', 'author' => 'a1', 'status' => 'published', 'date' => '2026-08-18', 'views' => 2840,
        'excerpt' => 'Move beyond lecture with five research-backed active learning techniques you can implement in any classroom tomorrow.',
        'cover' => 'https://images.pexels.com/photos/8197511/pexels-photo-8197511.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'body' => "Active learning isn't a buzzword — it's a well-studied shift from passive consumption to engaged participation. The research is clear: students who interact with material, rather than simply receive it, retain more and perform better.\n\n## 1. Think-Pair-Share\n\nPose a question, give 30 seconds of silent thinking time, then have students discuss with a neighbor before sharing out. It's low-prep, high-engagement, and works from kindergarten to graduate school.\n\n## 2. Poll Everywhere (or any live poll)\n\nA quick multiple-choice poll at the start of a topic surfaces misconceptions instantly. You can adjust your lesson on the fly instead of discovering gaps on the test.\n\n## 3. Jigsaw Reading\n\nSplit a longer text into sections. Each group masters one section, then teaches it to the class. Students become accountable for each other's learning — a powerful motivator.\n\n## 4. One-Minute Paper\n\nAt the end of a lesson, ask students to write the most important thing they learned and one question they still have. You get instant formative assessment; they get retrieval practice.\n\n## 5. Gallery Walk\n\nPost chart paper around the room with different prompts. Groups rotate, adding responses. It gets bodies moving and surfaces a wide range of thinking.\n\nNone of these require expensive technology or a complete redesign. Start with one, try it for a week, and watch engagement climb."],
    ['id' => 'p2', 'title' => 'How AI Tutoring Tools Are Changing Homework Help', 'category' => 'c2', 'author' => 'a3', 'status' => 'published', 'date' => '2026-08-15', 'views' => 4120,
        'excerpt' => "AI tutors are now available 24/7. Here's what schools should know about the benefits, the risks, and how to use them responsibly.",
        'cover' => 'https://images.pexels.com/photos/7013900/pexels-photo-7013900.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'body' => "AI tutoring tools have gone from novelty to mainstream in record time. A student stuck on a calculus problem at 10pm can now get a step-by-step explanation in seconds — not the next morning, not after a parent emails the teacher.\n\n## The upside\n\nThe best AI tutors don't just give answers; they scaffold. They ask guiding questions, check understanding, and adapt difficulty. For a motivated learner, that's a genuine acceleration.\n\n## The risk\n\nThe same tools can short-circuit learning when used as answer engines. A student who copies the output without engaging has practiced nothing. The difference is in how the tool is used, not whether it exists.\n\n## What schools can do\n\nTeach students to use AI as a thinking partner, not an answer key. Model good prompting in class. And give assignments that reward process — showing work, reflecting on mistakes — not just final answers.\n\nAI tutoring is a tool. Like any tool, its value depends on the hand that holds it."],
    ['id' => 'p3', 'title' => 'Building a Growth Mindset Classroom Culture', 'category' => 'c3', 'author' => 'a1', 'status' => 'published', 'date' => '2026-08-12', 'views' => 1980,
        'excerpt' => "A growth mindset isn't a poster on the wall. It's a culture built through daily language, feedback, and how mistakes are handled.",
        'cover' => 'https://images.pexels.com/photos/8617736/pexels-photo-8617736.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'body' => "Carol Dweck's research on growth mindset has been widely cited and widely misunderstood. The goal isn't to tell students \"you can do anything\" — it's to help them see ability as developable, not fixed.\n\n## Praise the process\n\nInstead of \"You're so smart,\" try \"I noticed how you tried three different strategies.\" Praise effort, strategy, and persistence — the things students control.\n\n## Normalize struggle\n\nWhen a task is hard, say so out loud. \"This is supposed to be challenging — that's how your brain grows.\" Struggle becomes a sign of learning, not a sign of deficiency.\n\n## Reframe mistakes\n\nMistakes are data, not verdicts. Use \"not yet\" instead of \"fail.\" Celebrate a wrong answer that led to a better question.\n\nA growth mindset culture is built in the small moments — the feedback you give, the language you use, the way you respond when a student is stuck."],
    ['id' => 'p4', 'title' => 'The Hybrid Classroom: A Practical Setup Guide', 'category' => 'c4', 'author' => 'a3', 'status' => 'published', 'date' => '2026-08-09', 'views' => 3200,
        'excerpt' => "Hybrid learning is here to stay. Here's how to set up your room, your tech, and your lessons for both in-person and remote students.",
        'cover' => 'https://images.pexels.com/photos/5212666/pexels-photo-5212666.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'body' => "Hybrid teaching — some students in the room, some remote — was a pandemic necessity that became a permanent option. Done well, it offers flexibility and access. Done poorly, it frustrates everyone.\n\n## The room\n\nPosition a camera so remote students can see both you and the board. A second display showing the remote participants lets you read their reactions. Audio matters more than video — invest in a good omnidirectional mic.\n\n## The tech\n\nUse a shared digital whiteboard both groups can edit. Keep a backchannel (chat or doc) for questions so remote students aren't an afterthought.\n\n## The lesson\n\nDesign for participation, not transmission. Breakout rooms that mix in-person and remote students force collaboration. Avoid long lectures — they're hardest on the people watching through a screen.\n\nHybrid isn't easier, but with the right setup it can be just as effective as a traditional classroom."],
    ['id' => 'p5', 'title' => 'Why School Funding Formulas Need a 2026 Update', 'category' => 'c5', 'author' => 'a4', 'status' => 'published', 'date' => '2026-08-05', 'views' => 1560,
        'excerpt' => "Most state funding formulas were designed for a different era. Here's what's broken and what policymakers are trying next.",
        'cover' => 'https://images.pexels.com/photos/10127242/pexels-photo-10127242.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'body' => "School funding formulas — the math that decides how much each district gets — are mostly decades old. They were built for a world of attendance-based enrollment, print textbooks, and no internet.\n\n## The problem with attendance-based funding\n\nWhen money follows daily attendance, a single sick day or a family move can cost a district thousands. It punishes the schools serving the most mobile and vulnerable students.\n\nWhat's replacing it: enrollment-based funding, where a district is funded for the students it serves, not the ones who showed up on a given Tuesday.\n\n## The weight question\n\nShould a student in poverty get more funding? A student with a disability? An English learner? Most formulas say yes, but the weights are often arbitrary — set in 1995 and never adjusted.\n\n## What's next\n\nSeveral states are experimenting with \"student-based\" formulas that attach money to individual students and their needs, rather than to programs or staffing ratios. Early results are promising.\n\nFunding isn't just an accounting question — it's a statement about who we think deserves what."],
    ['id' => 'p6', 'title' => 'Gamification in Education: Beyond Points and Badges', 'category' => 'c2', 'author' => 'a3', 'status' => 'published', 'date' => '2026-08-01', 'views' => 2750,
        'excerpt' => "Real gamification taps into autonomy, mastery, and purpose — not just a leaderboard. Here's how to do it meaningfully.",
        'cover' => 'https://images.pexels.com/photos/7548729/pexels-photo-7548729.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'body' => "Gamification got a bad reputation from apps that slapped points and badges onto everything and called it \"motivation.\" Done well, though, game design principles can make learning genuinely engaging.\n\n## What actually motivates\n\nSelf-Determination Theory tells us people are driven by autonomy, competence, and relatedness. A leaderboard hits none of those. Letting students choose their path, see their progress, and contribute to a team does.\n\n## Meaningful progress\n\nShow students where they are and where they're going. A visible skill tree (think Duolingo) turns abstract \"get better at writing\" into concrete, trackable steps.\n\n## Narrative and stakes\n\nWrap a unit in a story. \"You're a team of scientists solving a real problem.\" The content is the same; the frame makes it matter.\n\nGamification isn't about making school fun — it's about making progress visible and effort meaningful."],
    ['id' => 'p7', 'title' => 'Helping Struggling Readers: A Multi-Tiered Approach', 'category' => 'c3', 'author' => 'a1', 'status' => 'published', 'date' => '2026-07-28', 'views' => 2240,
        'excerpt' => 'One in five students has reading difficulties. A tiered support system catches them early and gives targeted help.',
        'cover' => 'https://images.pexels.com/photos/256455/pexels-photo-256455.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'body' => "Reading is the foundation of all later learning, yet roughly 20% of students struggle with it. The good news: we know what works. The challenge: doing it at scale.\n\n## Tier 1: Strong core instruction\n\nEvery student gets evidence-based reading instruction — explicit phonics, vocabulary, comprehension strategies, and lots of time actually reading. Most students will thrive here.\n\n## Tier 2: Targeted intervention\n\nStudents who need more get small-group instruction 3-4 times a week, focused on their specific gap. Progress is monitored every two weeks.\n\n## Tier 3: Intensive support\n\nThe few who still struggle get daily, individualized instruction from a specialist. This is not a permanent label — the goal is to close the gap and exit.\n\nThe tiered model works because it's proactive, not reactive. You don't wait for a student to fail; you catch the struggle early and respond."],
    ['id' => 'p8', 'title' => "Designing Online Courses That Don't Bore Students", 'category' => 'c4', 'author' => 'a3', 'status' => 'published', 'date' => '2026-07-22', 'views' => 3680,
        'excerpt' => "Online doesn't have to mean passive. Here's how to design an online course that keeps learners engaged from start to finish.",
        'cover' => 'https://images.pexels.com/photos/4144927/pexels-photo-4144927.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'body' => "The default online course is a video lecture plus a quiz. It's efficient to produce and boring to take. Students click through, retain little, and disengage.\n\n## Chunk the content\n\nNo video should exceed 6 minutes. Attention drops sharply after that. Break a 30-minute lecture into five focused segments with a quick check between each.\n\n## Make it interactive\n\nEmbed questions, polls, or reflection prompts inside videos. Even a simple \"pause and predict\" moment forces active processing.\n\n## Build community\n\nThe loneliest part of online learning is the lack of peers. Use discussion boards with real prompts (not \"post your thoughts\"), peer review, and live sessions.\n\n## Respect their time\n\nOnline learners are often fitting study around work and family. Be explicit about time commitments, keep modules self-contained, and let them control the pace.\n\nGood online design isn't about technology — it's about respecting the learner."],
    ['id' => 'p9', 'title' => 'The Science of Spaced Practice (and Why Cramming Fails)', 'category' => 'c3', 'author' => 'a2', 'status' => 'published', 'date' => '2026-07-18', 'views' => 2950,
        'excerpt' => "Cramming feels productive but research shows it's one of the least effective ways to learn. Here's what to do instead.",
        'cover' => 'https://images.pexels.com/photos/8199160/pexels-photo-8199160.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'body' => "Every student knows the feeling: a test tomorrow, a semester of material, one night to learn it. Cramming feels like learning. It isn't.\n\n## Why cramming fails\n\nMassed practice — studying one thing for hours — produces a feeling of fluency that fades fast. You can perform on tomorrow's test and forget it all by next week. The brain encodes nothing for the long term.\n\n## What works: spaced practice\n\nStudy the same material in shorter sessions spread over days or weeks. The effort of retrieving something you've partly forgotten is what builds durable memory.\n\n## How to implement it\n\nReview yesterday's material for 10 minutes at the start of today's study session. Mix in older material weekly. Use flashcards with an app that schedules reviews.\n\n## The hard part\n\nSpaced practice feels harder than cramming — you're retrieving, not rereading. That difficulty is the point. It's the cognitive equivalent of lifting weights.\n\nTeach students this, and you give them a tool that works for the rest of their lives."],
    ['id' => 'p10', 'title' => 'Equity in EdTech: Who Gets Left Behind?', 'category' => 'c5', 'author' => 'a4', 'status' => 'published', 'date' => '2026-07-14', 'views' => 1820,
        'excerpt' => "Edtech promises to democratize learning, but it can widen gaps when access isn't equal. Here's how schools can close the divide.",
        'cover' => 'https://images.pexels.com/photos/289737/pexels-photo-289737.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'body' => "The pandemic exposed a digital divide many assumed was closing. When schools went remote, some students had quiet rooms, fast internet, and their own devices. Others had none of those.\n\n## The access gap\n\nA device and broadband are table stakes. But access also means a space to work, an adult who can help, and a community that values school. Edtech can't fix all of that, but it shouldn't ignore it.\n\n## The design gap\n\nMany edtech tools are designed for the students who already have the most. They assume reliable internet, English fluency, and tech-savvy families. Tools that work offline, support multiple languages, and are usable on a phone aren't nice-to-haves — they're equity.\n\n## What schools can do\n\nAudit your tools for access assumptions. Provide devices and hotspots where needed. Choose platforms that work on the devices families actually have, not the ones you wish they had.\n\nEquity in edtech isn't about equal tools — it's about equal outcomes."],
    ['id' => 'p11', 'title' => 'Project-Based Learning: A Year-Long Case Study', 'category' => 'c1', 'author' => 'a1', 'status' => 'published', 'date' => '2026-07-10', 'views' => 2100,
        'excerpt' => "We followed one teacher's project-based learning classroom for a full year. Here's what worked, what didn't, and what she'd change.",
        'cover' => 'https://images.pexels.com/photos/4173338/pexels-photo-4173338.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'body' => "Project-based learning (PBL) sounds great in theory: students tackle real problems over weeks, building deep knowledge and skills. In practice, it's hard. We spent a year in one teacher's classroom to see what it really takes.\n\n## The setup\n\nMs. Rivera teaches 8th-grade science. She restructured her year around three projects: designing a community garden, building water filters, and proposing a local environmental policy.\n\n## What worked\n\nEngagement was dramatically higher. Students who'd been quiet all year became leaders. The depth of understanding — especially in the garden project, which ran for 10 weeks — was striking.\n\n## What didn't\n\nCoverage. PBL is slow. She covered less content than in a traditional year, and some standards got short shrift. Assessment was harder — a rubric can't capture everything a student learned.\n\n## What she'd change\n\nPlan assessment from day one. Build in more structured checkpoints. And accept that you can't do everything — choose depth in a few areas over shallow coverage of all.\n\nPBL isn't a silver bullet, but for the right unit and the right teacher, it's transformative."],
    ['id' => 'p12', 'title' => 'The Parent-School Partnership: What Research Tells Us', 'category' => 'c3', 'author' => 'a1', 'status' => 'draft', 'date' => '2026-07-05', 'views' => 0,
        'excerpt' => "Parent involvement matters — but not all involvement is equal. Here's what the research says actually moves the needle.",
        'cover' => 'https://images.pexels.com/photos/5303657/pexels-photo-5303657.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'body' => "Schools have long urged \"parent involvement,\" but the research is more specific: it's not any involvement that helps, it's the right kind.\n\n## What doesn't work\n\nGeneric \"be involved\" messaging. Attending the bake sale. Hovering over homework every night. These have little measurable impact on achievement.\n\n## What does work\n\nHigh expectations communicated at home. Reading with young children. Talking about school in ways that show you value it. These \"academic socialization\" behaviors predict achievement years later.\n\n## The school's role\n\nDon't just ask parents to show up. Give them specific, manageable things to do: ask your child about this topic, read this book together, check this one assignment. Make it easy to do the high-impact things.\n\n## The equity dimension\n\nNot all parents can attend daytime meetings or help with homework in English. Schools that partner well meet families where they are — flexible times, translation, and respect for different kinds of involvement.\n\nThe strongest predictor of student success isn't parent presence at school — it's parent expectations at home."],
];

$postIds = []; // mock key ('p1'..'p12') => real blog_posts.id
foreach ($postSeeds as $p) {
    $slug = Str::slugify($p['title']);
    $base = $slug;
    $suffix = 2;
    while (true) {
        $row = Database::selectOne('SELECT id FROM blog_posts WHERE slug = ?', [$slug]);
        if (!$row) {
            break;
        }
        // Already-seeded row for this exact title -> reuse it (idempotent re-run).
        $existingTitle = Database::selectOne('SELECT title FROM blog_posts WHERE id = ?', [$row['id']]);
        if ($existingTitle && $existingTitle['title'] === $p['title']) {
            $postIds[$p['id']] = (int) $row['id'];
            continue 2;
        }
        $slug = $base . '-' . $suffix;
        $suffix++;
    }

    $publishedDate = $p['status'] === 'published' ? ($p['date'] . ' 09:00:00') : null;

    Database::execute(
        'INSERT INTO blog_posts (title, slug, excerpt, body, cover_image, status, author_id, category_id, published_date, views, seo_title, seo_description, deleted_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)',
        [
            $p['title'],
            $slug,
            $p['excerpt'],
            $p['body'],
            $p['cover'],
            $p['status'],
            $authorIds[$p['author']],
            $categoryIds[$p['category']],
            $publishedDate,
            $p['views'],
            $p['title'],
            $p['excerpt'],
        ]
    );
    $postIds[$p['id']] = (int) Database::lastInsertId();
}

fwrite(STDOUT, "Seeded " . count($postIds) . " posts.\n");

// ---------------------------------------------------------------------
// 4. Comments (blog_comments) — the three MOCK_COMMENTS, all on p1.
//    Seeded as 'approved' since the mock UI shows them as already-visible.
// ---------------------------------------------------------------------

$commentSeeds = [
    ['post' => 'p1', 'name' => 'Jamie R.', 'email' => 'jamie.r@example.com', 'date' => '2026-08-19', 'body' => 'Tried Think-Pair-Share today and the engagement was night and day. Thanks for the concrete tips!'],
    ['post' => 'p1', 'name' => 'Alex T.',  'email' => 'alex.t@example.com',  'date' => '2026-08-20', 'body' => 'The one-minute paper is my favorite. I learn more from reading those than from the quiz grades.'],
    ['post' => 'p1', 'name' => 'Sam K.',   'email' => 'sam.k@example.com',   'date' => '2026-08-21', 'body' => 'Gallery walk takes more prep but the energy in the room is worth it.'],
];

$commentCount = 0;
foreach ($commentSeeds as $c) {
    $postId = $postIds[$c['post']];
    $row = Database::selectOne(
        'SELECT id FROM blog_comments WHERE post_id = ? AND author_email = ? AND body = ?',
        [$postId, $c['email'], $c['body']]
    );
    if ($row) {
        continue;
    }
    Database::execute(
        'INSERT INTO blog_comments (post_id, author_name, author_email, body, status, created_at) VALUES (?, ?, ?, ?, ?, ?)',
        [$postId, $c['name'], $c['email'], $c['body'], 'approved', $c['date'] . ' 10:00:00']
    );
    $commentCount++;
}

fwrite(STDOUT, "Seeded {$commentCount} comment(s) (skipped any already present).\n");

// ---------------------------------------------------------------------
// 5. Media (blog_media) — the eight MOCK_MEDIA items. These are the same
//    external Pexels URLs the mock frontend already used as post cover
//    images, stored as file_path so the admin media library has real
//    rows to list/delete-guard against, without needing actual uploaded
//    files on disk for demo/browsing purposes.
// ---------------------------------------------------------------------

$mediaSeeds = [
    ['filename' => 'classroom-students.jpg', 'url' => 'https://images.pexels.com/photos/8197511/pexels-photo-8197511.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', 'uploaded_by' => 'a1', 'date' => '2026-08-18'],
    ['filename' => 'teacher-helping.jpg',    'url' => 'https://images.pexels.com/photos/8617736/pexels-photo-8617736.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', 'uploaded_by' => 'a1', 'date' => '2026-08-12'],
    ['filename' => 'online-learning.jpg',    'url' => 'https://images.pexels.com/photos/7013900/pexels-photo-7013900.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', 'uploaded_by' => 'a3', 'date' => '2026-08-15'],
    ['filename' => 'empty-classroom.jpg',    'url' => 'https://images.pexels.com/photos/10127242/pexels-photo-10127242.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', 'uploaded_by' => 'a4', 'date' => '2026-08-05'],
    ['filename' => 'tablet-learning.jpg',    'url' => 'https://images.pexels.com/photos/7548729/pexels-photo-7548729.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', 'uploaded_by' => 'a3', 'date' => '2026-08-01'],
    ['filename' => 'whiteboard-math.jpg',    'url' => 'https://images.pexels.com/photos/6325967/pexels-photo-6325967.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', 'uploaded_by' => 'a2', 'date' => '2026-07-28'],
    ['filename' => 'library-books.jpg',      'url' => 'https://images.pexels.com/photos/256455/pexels-photo-256455.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', 'uploaded_by' => 'a1', 'date' => '2026-07-22'],
    ['filename' => 'parent-homework.jpg',    'url' => 'https://images.pexels.com/photos/5303657/pexels-photo-5303657.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', 'uploaded_by' => 'a1', 'date' => '2026-07-14'],
];

$mediaCount = 0;
foreach ($mediaSeeds as $m) {
    $row = Database::selectOne('SELECT id FROM blog_media WHERE filename = ?', [$m['filename']]);
    if ($row) {
        continue;
    }
    Database::execute(
        'INSERT INTO blog_media (filename, file_path, alt_text, uploaded_by, created_at) VALUES (?, ?, ?, ?, ?)',
        [$m['filename'], $m['url'], null, $authorIds[$m['uploaded_by']], $m['date'] . ' 08:00:00']
    );
    $mediaCount++;
}

fwrite(STDOUT, "Seeded {$mediaCount} media item(s) (skipped any already present).\n");

// ---------------------------------------------------------------------
// 6. Daily view stats (blog_post_views_daily) — a small spread of rows
//    per published post so the aggregation table isn't empty; total per
//    post roughly matches blog_posts.views (not exact — that's fine,
//    this table is for trend charts, not the source of truth for totals).
// ---------------------------------------------------------------------

$statsCount = 0;
foreach ($postSeeds as $p) {
    if ($p['status'] !== 'published') {
        continue;
    }
    $postId = $postIds[$p['id']];
    $publishedAt = strtotime($p['date']);
    $daysSpread = 5;
    $remaining = $p['views'];
    for ($i = 0; $i < $daysSpread; $i++) {
        $day = date('Y-m-d', $publishedAt + $i * 86400);
        $row = Database::selectOne('SELECT id FROM blog_post_views_daily WHERE post_id = ? AND view_date = ?', [$postId, $day]);
        if ($row) {
            continue;
        }
        $isLast = $i === $daysSpread - 1;
        $chunk = $isLast ? $remaining : (int) round($p['views'] / $daysSpread);
        $chunk = max(0, min($chunk, $remaining));
        $remaining -= $chunk;
        Database::execute(
            'INSERT INTO blog_post_views_daily (post_id, view_date, views) VALUES (?, ?, ?)',
            [$postId, $day, $chunk]
        );
        $statsCount++;
    }
}

fwrite(STDOUT, "Seeded {$statsCount} daily view-stat row(s).\n");

fwrite(STDOUT, "\nDone. Seed login credentials (password is the same for every account):\n");
fwrite(STDOUT, "  Password: {$seedPassword}\n");
foreach ($authorSeeds as $u) {
    fwrite(STDOUT, "  Author: {$u['email']}\n");
}
fwrite(STDOUT, "  Admin:  {$adminSeed['email']}\n");
