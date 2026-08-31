/* ============================================================
   mock-data.js — single source of truth for blog.skoolyst.com
   In a real app, a backend API replaces these arrays/objects.
   ============================================================ */

const MOCK_AUTHORS = [
  {
    id: 'a1',
    name: 'Sarah Chen',
    avatar: 'https://i.pravatar.cc/150?img=47',
    bio: 'Sarah is Skoolyst\u2019s lead content strategist. She writes about digital pedagogy, curriculum design, and the future of classroom technology.'
  },
  {
    id: 'a2',
    name: 'Marcus Johnson',
    avatar: 'https://i.pravatar.cc/150?img=12',
    bio: 'Marcus is a former high-school math teacher turned edtech writer. He focuses on STEM engagement and data-driven instruction.'
  },
  {
    id: 'a3',
    name: 'Priya Patel',
    avatar: 'https://i.pravatar.cc/150?img=45',
    bio: 'Priya covers online learning trends and the student experience. She holds a Master\u2019s in Instructional Design.'
  },
  {
    id: 'a4',
    name: 'David Kim',
    avatar: 'https://i.pravatar.cc/150?img=33',
    bio: 'David writes about education policy, funding, and the business of learning. He previously covered K-12 for a national outlet.'
  }
];

const MOCK_CATEGORIES = [
  { id: 'c1', name: 'Teaching Strategies', slug: 'teaching-strategies', description: 'Practical approaches for the modern classroom.', color: '#0f4077' },
  { id: 'c2', name: 'EdTech', slug: 'edtech', description: 'Technology that\u2019s reshaping how students learn.', color: '#4361ee' },
  { id: 'c3', name: 'Student Success', slug: 'student-success', description: 'Tips and research on helping every learner thrive.', color: '#0d9488' },
  { id: 'c4', name: 'Online Learning', slug: 'online-learning', description: 'Best practices for virtual and hybrid classrooms.', color: '#7c3aed' },
  { id: 'c5', name: 'Education Policy', slug: 'education-policy', description: 'Funding, standards, and the business of learning.', color: '#d97706' }
];

const MOCK_POSTS = [
  {
    id: 'p1',
    title: '5 Active Learning Strategies That Actually Work in 2026',
    slug: 'active-learning-strategies-2026',
    excerpt: 'Move beyond lecture with five research-backed active learning techniques you can implement in any classroom tomorrow.',
    body: [
      'Active learning isn\u2019t a buzzword \u2014 it\u2019s a well-studied shift from passive consumption to engaged participation. The research is clear: students who interact with material, rather than simply receive it, retain more and perform better.',
      '## 1. Think-Pair-Share',
      'Pose a question, give 30 seconds of silent thinking time, then have students discuss with a neighbor before sharing out. It\u2019s low-prep, high-engagement, and works from kindergarten to graduate school.',
      '## 2. Poll Everywhere (or any live poll)',
      'A quick multiple-choice poll at the start of a topic surfaces misconceptions instantly. You can adjust your lesson on the fly instead of discovering gaps on the test.',
      '## 3. Jigsaw Reading',
      'Split a longer text into sections. Each group masters one section, then teaches it to the class. Students become accountable for each other\u2019s learning \u2014 a powerful motivator.',
      '## 4. One-Minute Paper',
      'At the end of a lesson, ask students to write the most important thing they learned and one question they still have. You get instant formative assessment; they get retrieval practice.',
      '## 5. Gallery Walk',
      'Post chart paper around the room with different prompts. Groups rotate, adding responses. It gets bodies moving and surfaces a wide range of thinking.',
      'None of these require expensive technology or a complete redesign. Start with one, try it for a week, and watch engagement climb.'
    ].join('\n\n'),
    coverImage: 'https://images.pexels.com/photos/8197511/pexels-photo-8197511.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    category: 'c1',
    tags: ['active learning', 'engagement', 'pedagogy'],
    author: 'a1',
    status: 'published',
    publishedDate: '2026-08-18',
    views: 2840,
    readTimeMinutes: 6
  },
  {
    id: 'p2',
    title: 'How AI Tutoring Tools Are Changing Homework Help',
    slug: 'ai-tutoring-tools-homework-help',
    excerpt: 'AI tutors are now available 24/7. Here\u2019s what schools should know about the benefits, the risks, and how to use them responsibly.',
    body: [
      'AI tutoring tools have gone from novelty to mainstream in record time. A student stuck on a calculus problem at 10pm can now get a step-by-step explanation in seconds \u2014 not the next morning, not after a parent emails the teacher.',
      '## The upside',
      'The best AI tutors don\u2019t just give answers; they scaffold. They ask guiding questions, check understanding, and adapt difficulty. For a motivated learner, that\u2019s a genuine acceleration.',
      '## The risk',
      'The same tools can short-circuit learning when used as answer engines. A student who copies the output without engaging has practiced nothing. The difference is in how the tool is used, not whether it exists.',
      '## What schools can do',
      'Teach students to use AI as a thinking partner, not an answer key. Model good prompting in class. And give assignments that reward process \u2014 showing work, reflecting on mistakes \u2014 not just final answers.',
      'AI tutoring is a tool. Like any tool, its value depends on the hand that holds it.'
    ].join('\n\n'),
    coverImage: 'https://images.pexels.com/photos/7013900/pexels-photo-7013900.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    category: 'c2',
    tags: ['ai', 'edtech', 'homework'],
    author: 'a3',
    status: 'published',
    publishedDate: '2026-08-15',
    views: 4120,
    readTimeMinutes: 5
  },
  {
    id: 'p3',
    title: 'Building a Growth Mindset Classroom Culture',
    slug: 'building-growth-mindset-classroom',
    excerpt: 'A growth mindset isn\u2019t a poster on the wall. It\u2019s a culture built through daily language, feedback, and how mistakes are handled.',
    body: [
      'Carol Dweck\u2019s research on growth mindset has been widely cited and widely misunderstood. The goal isn\u2019t to tell students \u201cyou can do anything\u201d \u2014 it\u2019s to help them see ability as developable, not fixed.',
      '## Praise the process',
      'Instead of \u201cYou\u2019re so smart,\u201d try \u201cI noticed how you tried three different strategies.\u201d Praise effort, strategy, and persistence \u2014 the things students control.',
      '## Normalize struggle',
      'When a task is hard, say so out loud. \u201cThis is supposed to be challenging \u2014 that\u2019s how your brain grows.\u201d Struggle becomes a sign of learning, not a sign of deficiency.',
      '## Reframe mistakes',
      'Mistakes are data, not verdicts. Use \u201cnot yet\u201d instead of \u201cfail.\u201d Celebrate a wrong answer that led to a better question.',
      'A growth mindset culture is built in the small moments \u2014 the feedback you give, the language you use, the way you respond when a student is stuck.'
    ].join('\n\n'),
    coverImage: 'https://images.pexels.com/photos/8617736/pexels-photo-8617736.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    category: 'c3',
    tags: ['growth mindset', 'culture', 'feedback'],
    author: 'a1',
    status: 'published',
    publishedDate: '2026-08-12',
    views: 1980,
    readTimeMinutes: 7
  },
  {
    id: 'p4',
    title: 'The Hybrid Classroom: A Practical Setup Guide',
    slug: 'hybrid-classroom-setup-guide',
    excerpt: 'Hybrid learning is here to stay. Here\u2019s how to set up your room, your tech, and your lessons for both in-person and remote students.',
    body: [
      'Hybrid teaching \u2014 some students in the room, some remote \u2014 was a pandemic necessity that became a permanent option. Done well, it offers flexibility and access. Done poorly, it frustrates everyone.',
      '## The room',
      'Position a camera so remote students can see both you and the board. A second display showing the remote participants lets you read their reactions. Audio matters more than video \u2014 invest in a good omnidirectional mic.',
      '## The tech',
      'Use a shared digital whiteboard both groups can edit. Keep a backchannel (chat or doc) for questions so remote students aren\u2019t an afterthought.',
      '## The lesson',
      'Design for participation, not transmission. Breakout rooms that mix in-person and remote students force collaboration. Avoid long lectures \u2014 they\u2019re hardest on the people watching through a screen.',
      'Hybrid isn\u2019t easier, but with the right setup it can be just as effective as a traditional classroom.'
    ].join('\n\n'),
    coverImage: 'https://images.pexels.com/photos/5212666/pexels-photo-5212666.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    category: 'c4',
    tags: ['hybrid', 'setup', 'remote learning'],
    author: 'a3',
    status: 'published',
    publishedDate: '2026-08-09',
    views: 3200,
    readTimeMinutes: 8
  },
  {
    id: 'p5',
    title: 'Why School Funding Formulas Need a 2026 Update',
    slug: 'school-funding-formulas-2026',
    excerpt: 'Most state funding formulas were designed for a different era. Here\u2019s what\u2019s broken and what policymakers are trying next.',
    body: [
      'School funding formulas \u2014 the math that decides how much each district gets \u2014 are mostly decades old. They were built for a world of attendance-based enrollment, print textbooks, and no internet.',
      '## The problem with attendance-based funding',
      'When money follows daily attendance, a single sick day or a family move can cost a district thousands. It punishes the schools serving the most mobile and vulnerable students.',
      'What\u2019s replacing it: enrollment-based funding, where a district is funded for the students it serves, not the ones who showed up on a given Tuesday.',
      '## The weight question',
      'Should a student in poverty get more funding? A student with a disability? An English learner? Most formulas say yes, but the weights are often arbitrary \u2014 set in 1995 and never adjusted.',
      '## What\u2019s next',
      'Several states are experimenting with \u201cstudent-based\u201d formulas that attach money to individual students and their needs, rather than to programs or staffing ratios. Early results are promising.',
      'Funding isn\u2019t just an accounting question \u2014 it\u2019s a statement about who we think deserves what.'
    ].join('\n\n'),
    coverImage: 'https://images.pexels.com/photos/10127242/pexels-photo-10127242.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    category: 'c5',
    tags: ['funding', 'policy', 'equity'],
    author: 'a4',
    status: 'published',
    publishedDate: '2026-08-05',
    views: 1560,
    readTimeMinutes: 9
  },
  {
    id: 'p6',
    title: 'Gamification in Education: Beyond Points and Badges',
    slug: 'gamification-beyond-points-badges',
    excerpt: 'Real gamification taps into autonomy, mastery, and purpose \u2014 not just a leaderboard. Here\u2019s how to do it meaningfully.',
    body: [
      'Gamification got a bad reputation from apps that slapped points and badges onto everything and called it \u201cmotivation.\u201d Done well, though, game design principles can make learning genuinely engaging.',
      '## What actually motivates',
      'Self-Determination Theory tells us people are driven by autonomy, competence, and relatedness. A leaderboard hits none of those. Letting students choose their path, see their progress, and contribute to a team does.',
      '## Meaningful progress',
      'Show students where they are and where they\u2019re going. A visible skill tree (think Duolingo) turns abstract \u201cget better at writing\u201d into concrete, trackable steps.',
      '## Narrative and stakes',
      'Wrap a unit in a story. \u201cYou\u2019re a team of scientists solving a real problem.\u201d The content is the same; the frame makes it matter.',
      'Gamification isn\u2019t about making school fun \u2014 it\u2019s about making progress visible and effort meaningful.'
    ].join('\n\n'),
    coverImage: 'https://images.pexels.com/photos/7548729/pexels-photo-7548729.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    category: 'c2',
    tags: ['gamification', 'motivation', 'edtech'],
    author: 'a3',
    status: 'published',
    publishedDate: '2026-08-01',
    views: 2750,
    readTimeMinutes: 6
  },
  {
    id: 'p7',
    title: 'Helping Struggling Readers: A Multi-Tiered Approach',
    slug: 'helping-struggling-readers',
    excerpt: 'One in five students has reading difficulties. A tiered support system catches them early and gives targeted help.',
    body: [
      'Reading is the foundation of all later learning, yet roughly 20% of students struggle with it. The good news: we know what works. The challenge: doing it at scale.',
      '## Tier 1: Strong core instruction',
      'Every student gets evidence-based reading instruction \u2014 explicit phonics, vocabulary, comprehension strategies, and lots of time actually reading. Most students will thrive here.',
      '## Tier 2: Targeted intervention',
      'Students who need more get small-group instruction 3-4 times a week, focused on their specific gap. Progress is monitored every two weeks.',
      '## Tier 3: Intensive support',
      'The few who still struggle get daily, individualized instruction from a specialist. This is not a permanent label \u2014 the goal is to close the gap and exit.',
      'The tiered model works because it\u2019s proactive, not reactive. You don\u2019t wait for a student to fail; you catch the struggle early and respond.'
    ].join('\n\n'),
    coverImage: 'https://images.pexels.com/photos/256455/pexels-photo-256455.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    category: 'c3',
    tags: ['reading', 'intervention', 'literacy'],
    author: 'a1',
    status: 'published',
    publishedDate: '2026-07-28',
    views: 2240,
    readTimeMinutes: 7
  },
  {
    id: 'p8',
    title: 'Designing Online Courses That Don\u2019t Bore Students',
    slug: 'designing-online-courses',
    excerpt: 'Online doesn\u2019t have to mean passive. Here\u2019s how to design an online course that keeps learners engaged from start to finish.',
    body: [
      'The default online course is a video lecture plus a quiz. It\u2019s efficient to produce and boring to take. Students click through, retain little, and disengage.',
      '## Chunk the content',
      'No video should exceed 6 minutes. Attention drops sharply after that. Break a 30-minute lecture into five focused segments with a quick check between each.',
      '## Make it interactive',
      'Embed questions, polls, or reflection prompts inside videos. Even a simple \u201cpause and predict\u201d moment forces active processing.',
      '## Build community',
      'The loneliest part of online learning is the lack of peers. Use discussion boards with real prompts (not \u201cpost your thoughts\u201d), peer review, and live sessions.',
      '## Respect their time',
      'Online learners are often fitting study around work and family. Be explicit about time commitments, keep modules self-contained, and let them control the pace.',
      'Good online design isn\u2019t about technology \u2014 it\u2019s about respecting the learner.'
    ].join('\n\n'),
    coverImage: 'https://images.pexels.com/photos/4144927/pexels-photo-4144927.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    category: 'c4',
    tags: ['online courses', 'design', 'engagement'],
    author: 'a3',
    status: 'published',
    publishedDate: '2026-07-22',
    views: 3680,
    readTimeMinutes: 5
  },
  {
    id: 'p9',
    title: 'The Science of Spaced Practice (and Why Cramming Fails)',
    slug: 'spaced-practice-science',
    excerpt: 'Cramming feels productive but research shows it\u2019s one of the least effective ways to learn. Here\u2019s what to do instead.',
    body: [
      'Every student knows the feeling: a test tomorrow, a semester of material, one night to learn it. Cramming feels like learning. It isn\u2019t.',
      '## Why cramming fails',
      'Massed practice \u2014 studying one thing for hours \u2014 produces a feeling of fluency that fades fast. You can perform on tomorrow\u2019s test and forget it all by next week. The brain encodes nothing for the long term.',
      '## What works: spaced practice',
      'Study the same material in shorter sessions spread over days or weeks. The effort of retrieving something you\u2019ve partly forgotten is what builds durable memory.',
      '## How to implement it',
      'Review yesterday\u2019s material for 10 minutes at the start of today\u2019s study session. Mix in older material weekly. Use flashcards with an app that schedules reviews.',
      '## The hard part',
      'Spaced practice feels harder than cramming \u2014 you\u2019re retrieving, not rereading. That difficulty is the point. It\u2019s the cognitive equivalent of lifting weights.',
      'Teach students this, and you give them a tool that works for the rest of their lives.'
    ].join('\n\n'),
    coverImage: 'https://images.pexels.com/photos/8199160/pexels-photo-8199160.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    category: 'c3',
    tags: ['memory', 'study skills', 'research'],
    author: 'a2',
    status: 'published',
    publishedDate: '2026-07-18',
    views: 2950,
    readTimeMinutes: 6
  },
  {
    id: 'p10',
    title: 'Equity in EdTech: Who Gets Left Behind?',
    slug: 'equity-in-edtech',
    excerpt: 'Edtech promises to democratize learning, but it can widen gaps when access isn\u2019t equal. Here\u2019s how schools can close the divide.',
    body: [
      'The pandemic exposed a digital divide many assumed was closing. When schools went remote, some students had quiet rooms, fast internet, and their own devices. Others had none of those.',
      '## The access gap',
      'A device and broadband are table stakes. But access also means a space to work, an adult who can help, and a community that values school. Edtech can\u2019t fix all of that, but it shouldn\u2019t ignore it.',
      '## The design gap',
      'Many edtech tools are designed for the students who already have the most. They assume reliable internet, English fluency, and tech-savvy families. Tools that work offline, support multiple languages, and are usable on a phone aren\u2019t nice-to-haves \u2014 they\u2019re equity.',
      '## What schools can do',
      'Audit your tools for access assumptions. Provide devices and hotspots where needed. Choose platforms that work on the devices families actually have, not the ones you wish they had.',
      'Equity in edtech isn\u2019t about equal tools \u2014 it\u2019s about equal outcomes.'
    ].join('\n\n'),
    coverImage: 'https://images.pexels.com/photos/289737/pexels-photo-289737.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    category: 'c5',
    tags: ['equity', 'access', 'digital divide'],
    author: 'a4',
    status: 'published',
    publishedDate: '2026-07-14',
    views: 1820,
    readTimeMinutes: 8
  },
  {
    id: 'p11',
    title: 'Project-Based Learning: A Year-Long Case Study',
    slug: 'project-based-learning-case-study',
    excerpt: 'We followed one teacher\u2019s project-based learning classroom for a full year. Here\u2019s what worked, what didn\u2019t, and what she\u2019d change.',
    body: [
      'Project-based learning (PBL) sounds great in theory: students tackle real problems over weeks, building deep knowledge and skills. In practice, it\u2019s hard. We spent a year in one teacher\u2019s classroom to see what it really takes.',
      '## The setup',
      'Ms. Rivera teaches 8th-grade science. She restructured her year around three projects: designing a community garden, building water filters, and proposing a local environmental policy.',
      '## What worked',
      'Engagement was dramatically higher. Students who\u2019d been quiet all year became leaders. The depth of understanding \u2014 especially in the garden project, which ran for 10 weeks \u2014 was striking.',
      '## What didn\u2019t',
      'Coverage. PBL is slow. She covered less content than in a traditional year, and some standards got short shrift. Assessment was harder \u2014 a rubric can\u2019t capture everything a student learned.',
      '## What she\u2019d change',
      'Plan assessment from day one. Build in more structured checkpoints. And accept that you can\u2019t do everything \u2014 choose depth in a few areas over shallow coverage of all.',
      'PBL isn\u2019t a silver bullet, but for the right unit and the right teacher, it\u2019s transformative.'
    ].join('\n\n'),
    coverImage: 'https://images.pexels.com/photos/4173338/pexels-photo-4173338.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    category: 'c1',
    tags: ['pbl', 'case study', 'science'],
    author: 'a1',
    status: 'published',
    publishedDate: '2026-07-10',
    views: 2100,
    readTimeMinutes: 9
  },
  {
    id: 'p12',
    title: 'The Parent-School Partnership: What Research Tells Us',
    slug: 'parent-school-partnership-research',
    excerpt: 'Parent involvement matters \u2014 but not all involvement is equal. Here\u2019s what the research says actually moves the needle.',
    body: [
      'Schools have long urged \u201cparent involvement,\u201d but the research is more specific: it\u2019s not any involvement that helps, it\u2019s the right kind.',
      '## What doesn\u2019t work',
      'Generic \u201cbe involved\u201d messaging. Attending the bake sale. Hovering over homework every night. These have little measurable impact on achievement.',
      '## What does work',
      'High expectations communicated at home. Reading with young children. Talking about school in ways that show you value it. These \u201cacademic socialization\u201d behaviors predict achievement years later.',
      '## The school\u2019s role',
      'Don\u2019t just ask parents to show up. Give them specific, manageable things to do: ask your child about this topic, read this book together, check this one assignment. Make it easy to do the high-impact things.',
      '## The equity dimension',
      'Not all parents can attend daytime meetings or help with homework in English. Schools that partner well meet families where they are \u2014 flexible times, translation, and respect for different kinds of involvement.',
      'The strongest predictor of student success isn\u2019t parent presence at school \u2014 it\u2019s parent expectations at home.'
    ].join('\n\n'),
    coverImage: 'https://images.pexels.com/photos/5303657/pexels-photo-5303657.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    category: 'c3',
    tags: ['parents', 'partnership', 'research'],
    author: 'a1',
    status: 'draft',
    publishedDate: '2026-07-05',
    views: 0,
    readTimeMinutes: 7
  }
];

const MOCK_STATS = {
  totalPosts: MOCK_POSTS.length,
  publishedPosts: MOCK_POSTS.filter(p => p.status === 'published').length,
  draftPosts: MOCK_POSTS.filter(p => p.status === 'draft').length,
  totalViews: MOCK_POSTS.reduce((sum, p) => sum + p.views, 0),
  totalCategories: MOCK_CATEGORIES.length,
  totalAuthors: MOCK_AUTHORS.length,
  monthlyViews: [
    { month: 'Jan', views: 4200 },
    { month: 'Feb', views: 5100 },
    { month: 'Mar', views: 6300 },
    { month: 'Apr', views: 5800 },
    { month: 'May', views: 7200 },
    { month: 'Jun', views: 8400 },
    { month: 'Jul', views: 9100 },
    { month: 'Aug', views: 10800 }
  ]
};

const MOCK_COMMENTS = [
  { id: 'cm1', postId: 'p1', author: 'Jamie R.', avatar: 'https://i.pravatar.cc/80?img=15', date: '2026-08-19', body: 'Tried Think-Pair-Share today and the engagement was night and day. Thanks for the concrete tips!' },
  { id: 'cm2', postId: 'p1', author: 'Alex T.', avatar: 'https://i.pravatar.cc/80?img=22', date: '2026-08-20', body: 'The one-minute paper is my favorite. I learn more from reading those than from the quiz grades.' },
  { id: 'cm3', postId: 'p1', author: 'Sam K.', avatar: 'https://i.pravatar.cc/80?img=8', date: '2026-08-21', body: 'Gallery walk takes more prep but the energy in the room is worth it.' }
];

const MOCK_MEDIA = [
  { id: 'm1', name: 'classroom-students.jpg', url: 'https://images.pexels.com/photos/8197511/pexels-photo-8197511.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', size: '1.2 MB', uploaded: '2026-08-18' },
  { id: 'm2', name: 'teacher-helping.jpg', url: 'https://images.pexels.com/photos/8617736/pexels-photo-8617736.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', size: '980 KB', uploaded: '2026-08-12' },
  { id: 'm3', name: 'online-learning.jpg', url: 'https://images.pexels.com/photos/7013900/pexels-photo-7013900.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', size: '1.4 MB', uploaded: '2026-08-15' },
  { id: 'm4', name: 'empty-classroom.jpg', url: 'https://images.pexels.com/photos/10127242/pexels-photo-10127242.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', size: '1.1 MB', uploaded: '2026-08-05' },
  { id: 'm5', name: 'tablet-learning.jpg', url: 'https://images.pexels.com/photos/7548729/pexels-photo-7548729.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', size: '1.3 MB', uploaded: '2026-08-01' },
  { id: 'm6', name: 'whiteboard-math.jpg', url: 'https://images.pexels.com/photos/6325967/pexels-photo-6325967.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', size: '1.0 MB', uploaded: '2026-07-28' },
  { id: 'm7', name: 'library-books.jpg', url: 'https://images.pexels.com/photos/256455/pexels-photo-256455.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', size: '1.5 MB', uploaded: '2026-07-22' },
  { id: 'm8', name: 'parent-homework.jpg', url: 'https://images.pexels.com/photos/5303657/pexels-photo-5303657.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', size: '1.2 MB', uploaded: '2026-07-14' }
];

/* ---- Helpers for looking up related data ---- */

function getAuthorById(id) {
  return MOCK_AUTHORS.find(a => a.id === id) || MOCK_AUTHORS[0];
}

function getCategoryById(id) {
  return MOCK_CATEGORIES.find(c => c.id === id) || MOCK_CATEGORIES[0];
}

function getPublishedPosts() {
  return MOCK_POSTS.filter(p => p.status === 'published');
}

function getPostById(id) {
  return MOCK_POSTS.find(p => p.id === id || p.slug === id);
}

function getPostsByCategory(catId) {
  return getPublishedPosts().filter(p => p.category === catId);
}

function getRelatedPosts(post, limit) {
  const n = limit || 3;
  return getPublishedPosts()
    .filter(p => p.id !== post.id && p.category === post.category)
    .slice(0, n);
}

function formatDate(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function escapeHtml(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/* Expose globally for non-module scripts */
window.MOCK_AUTHORS = MOCK_AUTHORS;
window.MOCK_CATEGORIES = MOCK_CATEGORIES;
window.MOCK_POSTS = MOCK_POSTS;
window.MOCK_STATS = MOCK_STATS;
window.MOCK_COMMENTS = MOCK_COMMENTS;
window.MOCK_MEDIA = MOCK_MEDIA;
window.getAuthorById = getAuthorById;
window.getCategoryById = getCategoryById;
window.getPublishedPosts = getPublishedPosts;
window.getPostById = getPostById;
window.getPostsByCategory = getPostsByCategory;
window.getRelatedPosts = getRelatedPosts;
window.formatDate = formatDate;
window.escapeHtml = escapeHtml;
