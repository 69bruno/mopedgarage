# UTF8MB4-Absicherung für Mopedgarage

Dieses Paket bereitet die Erweiterung auf eine saubere UTF8MB4-Umstellung vor.

## Warum nicht als blinde Migration?
In deinem Projekt gab es bereits Zwischenstände und Umbauten. Damit keine falschen Tabellen verändert werden,
ist die sicherste Variante hier eine gezielte Prüfung gegen den realen Ist-Zustand der Installation.

## 1) Tabellen und Collation prüfen

```sql
SELECT TABLE_NAME, TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME LIKE '%mopedgarage%';
```

## 2) Textspalten prüfen

```sql
SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME, DATA_TYPE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME LIKE '%mopedgarage%'
  AND DATA_TYPE IN ('char', 'varchar', 'text', 'mediumtext', 'longtext');
```

## 3) Auf utf8mb4 vereinheitlichen

Ersetze `phpbb_...` durch die realen Tabellennamen deiner Installation.

```sql
ALTER TABLE phpbb_mopedgarage_bikes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE phpbb_mopedgarage_images CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 4) Testdaten

Danach gezielt diese Werte speichern und wieder ausgeben:

- weiß
- grün
- Straße
- Größe
- Fußrasten
- Ölgekühlt

## 5) Cache leeren

Nach Datenbank- und Sprachänderungen immer den phpBB-Cache leeren.
