-- =============================================
-- snippet_vault.sql
-- Run this once in phpMyAdmin to set up the DB
-- =============================================

-- Create the database
CREATE DATABASE IF NOT EXISTS snippet_vault;
USE snippet_vault;

-- ---- SNIPPETS TABLE ----
-- Stores the main snippet metadata (no code column — code lives in snippet_files)
CREATE TABLE snippets (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(100) NOT NULL,
    language    VARCHAR(50)  NOT NULL,          -- primary language for the badge
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---- SNIPPET_FILES TABLE ----
-- Each snippet can have multiple files (html, css, js etc.)
-- One row = one file belonging to a snippet
CREATE TABLE snippet_files (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    snippet_id INT NOT NULL,
    filename   VARCHAR(100) NOT NULL,           -- e.g. "navbar.html", "style.css"
    code       LONGTEXT NOT NULL,               -- the actual file contents
    sort_order INT DEFAULT 0,                   -- controls display order of tabs

    -- If the parent snippet is deleted, delete its files too
    FOREIGN KEY (snippet_id) REFERENCES snippets(id) ON DELETE CASCADE
);

-- ---- TAGS TABLE ----
-- Stores unique tag names
CREATE TABLE tags (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- ---- SNIPPET_TAGS TABLE (Junction Table) ----
-- Links snippets to tags (many-to-many)
CREATE TABLE snippet_tags (
    snippet_id INT NOT NULL,
    tag_id     INT NOT NULL,
    PRIMARY KEY (snippet_id, tag_id),
    FOREIGN KEY (snippet_id) REFERENCES snippets(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id)     REFERENCES tags(id)     ON DELETE CASCADE
);
