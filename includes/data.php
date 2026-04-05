<?php

declare(strict_types=1);

$skins = [
    [
        'slug' => 'notch',
        'name' => 'Notch',
        'uuid' => '069a79f444e94726a5befca90e38aaf5',
        'owner' => 'Notch',
        'likes' => 1824,
        'views' => 11890,
        'variant' => 'Classic',
        'tags' => ['Trending', 'Popular', 'Premium'],
        'profiles' => ['Notch', 'NotchLive', 'NotchDev', 'NotchMC'],
        'description' => 'Official Minecraft premium skin loaded from Mojang texture services.',
        'accent' => '#7ef59a',
        'texture_hash' => '292009a4925b58f02c77dadc3ecef07ea4c7472f64e0fdc32ce5522489362680',
    ],
    [
        'slug' => 'jeb',
        'name' => 'jeb_',
        'uuid' => '853c80ef3c3749fdaa49938b674adae6',
        'owner' => 'jeb_',
        'likes' => 1260,
        'views' => 9022,
        'variant' => 'Classic',
        'tags' => ['Classic', 'Developer'],
        'profiles' => ['jeb_', 'jebDev'],
        'description' => 'Official Mojang texture for one of the most iconic Minecraft accounts.',
        'accent' => '#f1c27d',
        'texture_hash' => '7fd9ba42a7c81eeea22f1524271ae85a8e045ce0af5a6ae16c6406ae917e68b5',
    ],
    [
        'slug' => 'dinnerbone',
        'name' => 'Dinnerbone',
        'uuid' => '61699b2ed3274a019f1e0ea8c3f06bc6',
        'owner' => 'Dinnerbone',
        'likes' => 874,
        'views' => 6540,
        'variant' => 'Classic',
        'tags' => ['New', 'Mojang'],
        'profiles' => ['Dinnerbone', 'BoneFlip'],
        'description' => 'Official Minecraft texture retrieved from Mojang session services.',
        'accent' => '#80d0ff',
        'texture_hash' => '50c410fad8d9d8825ad56b0e443e2777a6b46bfa20dacd1d2f55edc71fbeb06d',
    ],
    [
        'slug' => 'aphmau',
        'name' => 'Aphmau',
        'uuid' => 'c82906afbdcf436c80c6216e5228763b',
        'owner' => 'Aphmau',
        'likes' => 2050,
        'views' => 15220,
        'variant' => 'Classic',
        'tags' => ['Featured', 'Creator'],
        'profiles' => ['Aphmau', 'AphmauYT', 'AphmauLive'],
        'description' => 'Real premium Minecraft profile texture used for creator-style showcase cards.',
        'accent' => '#ffb347',
        'texture_hash' => 'ddb9867071f7ab1e32d52c1e83f440a3370de6393caaaef35202923292e5e0f2',
    ],
    [
        'slug' => 'dream',
        'name' => 'Dream',
        'uuid' => 'ec70bcaf702f4bb8b48d276fa52a780c',
        'owner' => 'Dream',
        'likes' => 930,
        'views' => 7311,
        'variant' => 'Slim',
        'tags' => ['Starter', 'Slim'],
        'profiles' => ['Dream'],
        'description' => 'Official Dream skin texture loaded directly from Mojang.',
        'accent' => '#77b255',
        'texture_hash' => 'ca93f6fc40488f1877cda94a830b54e9f6f54ab58a5453bad5c947726dd1f473',
    ],
    [
        'slug' => 'skeppy',
        'name' => 'Skeppy',
        'uuid' => '8e176c5ac26d4c148efe77b598b8b3ea',
        'owner' => 'Skeppy',
        'likes' => 1465,
        'views' => 10041,
        'variant' => 'Classic',
        'tags' => ['Trending', 'Creator'],
        'profiles' => ['Skeppy', 'SkeppyLive', 'SkeppyClips'],
        'description' => 'Official skin texture for Skeppy, ideal for proving the Mojang texture pipeline.',
        'accent' => '#9d7dff',
        'texture_hash' => '8c5854ba208757e827a6ea9b91189fae1afb4d58d2364bf915a2e5f084355d85',
    ],
    [
        'slug' => 'tommyinnit',
        'name' => 'TommyInnit',
        'uuid' => 'e80e8194323e414298515e1bcb8a3508',
        'owner' => 'TommyInnit',
        'likes' => 1199,
        'views' => 8088,
        'variant' => 'Classic',
        'tags' => ['Popular', 'Creator'],
        'profiles' => ['TommyInnit', 'TommyLive'],
        'description' => 'Official TommyInnit skin texture fetched from Mojang.',
        'accent' => '#73d673',
        'texture_hash' => 'eb84f22489b6b5e426fc8c0c3b51daa002179e1c2e0767a552a22fc18d574d56',
    ],
    [
        'slug' => 'badboyhalo',
        'name' => 'BadBoyHalo',
        'uuid' => '26bdff37fec848f1980f66bf69ee751c',
        'owner' => 'BadBoyHalo',
        'likes' => 1672,
        'views' => 12555,
        'variant' => 'Classic',
        'tags' => ['Featured', 'Creator'],
        'profiles' => ['BadBoyHalo', 'BBH'],
        'description' => 'Official BadBoyHalo texture to demonstrate real Minecraft skin loading.',
        'accent' => '#ff6b6b',
        'texture_hash' => '595db8d8054a5114a0cab856ce09763c1c59ee89a8cb4e9d91dd7cad8bb3b96e',
    ],
];

$catalogHandles = [
    'Nova', 'Blaze', 'Pixel', 'Craft', 'Echo', 'Frost', 'Quartz', 'Vex', 'Rune', 'Comet',
    'Astra', 'Volt', 'Shadow', 'Drift', 'Flare', 'Ember', 'Luna', 'Neo', 'Ghost', 'Turbo',
];

$catalogTags = [
    ['Animated', '3D'],
    ['Battlepass', 'Featured'],
    ['Creator', 'Popular'],
    ['PvP', 'Trending'],
    ['Survival', 'Seasonal'],
    ['Neon', 'Premium'],
    ['Fantasy', 'Collector'],
    ['Esports', 'Hot'],
];

foreach (range(1, 300) as $index) {
    $template = $skins[($index - 1) % count($skins)];
    $handle = $catalogHandles[($index - 1) % count($catalogHandles)] . $index;
    $tags = array_values(array_unique(array_merge(
        $template['tags'],
        $catalogTags[($index - 1) % count($catalogTags)],
        [$index % 2 === 0 ? 'Studio' : 'Verified']
    )));

    $skins[] = [
        'slug' => 'skinforge-' . strtolower($handle),
        'name' => $handle,
        'uuid' => $template['uuid'],
        'owner' => $handle,
        'likes' => $template['likes'] + ($index * 17),
        'views' => $template['views'] + ($index * 93),
        'variant' => $index % 3 === 0 ? 'Slim' : $template['variant'],
        'tags' => $tags,
        'profiles' => [
            $handle,
            $handle . 'Live',
            $handle . 'Studio',
            $handle . 'Realm',
        ],
        'description' => 'Curated SkinForge catalog entry built from Mojang-backed texture references with richer presentation for production-ready browsing.',
        'accent' => $index % 2 === 0 ? '#7ef59a' : ($index % 3 === 0 ? '#80d0ff' : '#ffb347'),
        'texture_hash' => $template['texture_hash'],
    ];
}

$capes = [
    ['name' => 'Cherry Blossom 2024', 'owner' => 'SkinForge', 'accent' => '#ff7eb6', 'rarity' => 'Mythic', 'season' => 'Spring Drop'],
    ['name' => 'MCC 15 Cape', 'owner' => 'Minecraft Events', 'accent' => '#ffaa4d', 'rarity' => 'Epic', 'season' => 'Championship'],
    ['name' => 'Purple Heart', 'owner' => 'Community', 'accent' => '#9d7dff', 'rarity' => 'Rare', 'season' => 'Community'],
    ['name' => 'Migrator 2021', 'owner' => 'Mojang', 'accent' => '#67d1ff', 'rarity' => 'Legendary', 'season' => 'Migration'],
    ['name' => 'Vanilla 2024', 'owner' => 'Marketplace', 'accent' => '#7ef59a', 'rarity' => 'Epic', 'season' => 'Vanilla'],
    ['name' => 'TikTok Promo', 'owner' => 'Brand Campaign', 'accent' => '#2ed3c6', 'rarity' => 'Rare', 'season' => 'Promo'],
];

$capeHandles = ['Aurora', 'Nebula', 'Ember', 'Volt', 'Quartz', 'Obsidian', 'Drift', 'Prism', 'Nova', 'Bloom'];
$capeSeasons = ['Creator Series', 'Winter Event', 'Summer Pass', 'Arena Split', 'Legends Week'];
$capeRarities = ['Rare', 'Epic', 'Legendary', 'Mythic'];

foreach (range(1, 18) as $capeIndex) {
    $baseCape = $capes[($capeIndex - 1) % count($capes)];
    $capes[] = [
        'name' => $capeHandles[($capeIndex - 1) % count($capeHandles)] . ' Cape ' . $capeIndex,
        'owner' => $baseCape['owner'],
        'accent' => $baseCape['accent'],
        'rarity' => $capeRarities[($capeIndex - 1) % count($capeRarities)],
        'season' => $capeSeasons[($capeIndex - 1) % count($capeSeasons)],
    ];
}

$servers = [
    [
        'name' => 'mc.hypixel.net',
        'players' => '58,861',
        'status' => 'Online',
        'version' => '1.8 - 1.21',
        'tags' => ['Minigames', 'Popular'],
    ],
    [
        'name' => 'play.cubecraft.net',
        'players' => '12,104',
        'status' => 'Online',
        'version' => '1.20+',
        'tags' => ['Bedwars', 'Skyblock'],
    ],
    [
        'name' => 'mc.mineberry.org',
        'players' => '3,842',
        'status' => 'Online',
        'version' => '1.21',
        'tags' => ['Survival', 'Economy'],
    ],
];

$featuredProfiles = [
    ['name' => 'Notch', 'score' => '11890'],
    ['name' => 'jeb_', 'score' => '9022'],
    ['name' => 'Aphmau', 'score' => '15220'],
    ['name' => 'Skeppy', 'score' => '10041'],
];

$popularProfiles = [
    ['name' => 'Dream', 'score' => '7311'],
    ['name' => 'TommyInnit', 'score' => '8088'],
    ['name' => 'BadBoyHalo', 'score' => '12555'],
    ['name' => 'Dinnerbone', 'score' => '6540'],
    ['name' => 'Aphmau', 'score' => '4330'],
];

$recentSearches = [
    ['query' => 'Notch', 'source' => 'Homepage', 'time' => 'just now'],
    ['query' => 'Dream', 'source' => 'Skin detail', 'time' => '2 min ago'],
    ['query' => 'TommyInnit', 'source' => 'Search bar', 'time' => '5 min ago'],
    ['query' => 'BadBoyHalo', 'source' => 'Popular list', 'time' => '8 min ago'],
];

$profileHighlights = [
    [
        'name' => 'Aphmau',
        'handle' => '@aphmau',
        'headline' => 'Creator profile with saved renders, liked skins and premium inventory.',
        'followers' => '128K',
        'collections' => 24,
        'likes' => '9.4K',
        'status' => 'Creator',
    ],
    [
        'name' => 'Skeppy',
        'handle' => '@skeppy',
        'headline' => 'Competitive profile focused on PvP-ready skins and high-contrast textures.',
        'followers' => '92K',
        'collections' => 18,
        'likes' => '7.8K',
        'status' => 'Verified',
    ],
    [
        'name' => 'Dream',
        'handle' => '@dream',
        'headline' => 'Fast-access profile card for downloads, saved looks and scene-ready renders.',
        'followers' => '140K',
        'collections' => 31,
        'likes' => '11.2K',
        'status' => 'Elite',
    ],
];

$faq = [
    [
        'question' => 'How does the search work?',
        'answer' => 'In the production version, each nickname search can query the Mojang API, normalize the UUID, update profile-skin relations in MySQL, and immediately expose that data in home, skin lists, and detail pages.',
    ],
    [
        'question' => 'Can multiple premium profiles use the same skin?',
        'answer' => 'Yes. The platform links many profiles to one skin entry so visitors can see how many premium nicknames are currently using that same skin.',
    ],
    [
        'question' => 'Why is SkinForge useful?',
        'answer' => 'It proves design direction, page structure, UI polish, and core product understanding before connecting the real backend and database.',
    ],
];
