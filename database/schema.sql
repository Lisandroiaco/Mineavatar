CREATE TABLE skins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL,
    texture_hash VARCHAR(128) NOT NULL UNIQUE,
    uuid CHAR(32) NOT NULL,
    skin_name VARCHAR(120) NOT NULL,
    owner_name VARCHAR(120) NOT NULL,
    variant VARCHAR(20) NOT NULL DEFAULT 'Classic',
    description TEXT NOT NULL,
    likes_count INT NOT NULL DEFAULT 0,
    views_count INT NOT NULL DEFAULT 0,
    INDEX idx_skins_slug (slug),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_name VARCHAR(120) NOT NULL UNIQUE,
    uuid CHAR(32) NOT NULL,
    INDEX idx_profiles_uuid (uuid),
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE skin_profiles (
    skin_id INT NOT NULL,
    profile_id INT NOT NULL,
    PRIMARY KEY (skin_id, profile_id),
    CONSTRAINT fk_skin_profiles_skin FOREIGN KEY (skin_id) REFERENCES skins(id) ON DELETE CASCADE,
    CONSTRAINT fk_skin_profiles_profile FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE
);

CREATE TABLE servers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_name VARCHAR(255) NOT NULL,
    version_label VARCHAR(80) NOT NULL,
    players_online INT NOT NULL DEFAULT 0,
    status_label VARCHAR(40) NOT NULL DEFAULT 'Online'
);
