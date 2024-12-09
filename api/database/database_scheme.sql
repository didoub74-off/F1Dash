-- Database schema for SQLite
CREATE TABLE IF NOT EXISTS meetings
(
    id              TEXT PRIMARY KEY,
    name            TEXT    NOT NULL,
    championship_id TEXT    NOT NULL,
    circuit_id      TEXT    NOT NULL,
    round           INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS championships
(
    id   TEXT PRIMARY KEY,
    name TEXT    NOT NULL,
    year INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS sessions
(
    id         TEXT PRIMARY KEY,
    name       TEXT NOT NULL,
    meeting_id TEXT NOT NULL,
    type       TEXT NOT NULL,
    date       TEXT NOT NULL,
    time       TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS laps
(
    id         TEXT PRIMARY KEY,
    session_id TEXT NOT NULL,
    driver_id  TEXT NOT NULL,
    team_id    TEXT NOT NULL,
    time       REAL NOT NULL -- Store time in seconds (1:35.123 -> 95.123)
);

CREATE TABLE IF NOT EXISTS teams
(
    id          TEXT PRIMARY KEY,
    name        TEXT NOT NULL,
    nationality TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS drivers
(
    id          TEXT PRIMARY KEY,
    name        TEXT NOT NULL,
    dob         TEXT NOT NULL,
    nationality TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS circuits
(
    id      TEXT PRIMARY KEY,
    name    TEXT NOT NULL,
    country TEXT NOT NULL,
    city    TEXT NOT NULL,
    length  REAL NOT NULL
);