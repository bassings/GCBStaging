#!/usr/bin/env python3
"""Fix image URLs in SQLite WordPress database: .jpg/.jpeg/.png → .webp
Only targets internal upload paths (wp-content/uploads/YYYY/MM/)"""

import sqlite3
import re
import sys

db_path = sys.argv[1] if len(sys.argv) > 1 else 'wp-content/database/.ht.sqlite'
dry_run = '--dry-run' in sys.argv

conn = sqlite3.connect(db_path)
conn.row_factory = sqlite3.Row
cur = conn.cursor()

# Pattern: only match wp-content/uploads/YYYY/MM/filename.ext
pattern = re.compile(r'(wp-content/uploads/20\d{2}/\d{2}/[^"\'\s&?]+)\.(jpg|jpeg|png)', re.IGNORECASE)

# Find all posts with upload image references
cur.execute("""
    SELECT ID, post_content FROM wp_posts 
    WHERE post_content LIKE '%wp-content/uploads/20%.jpg%'
       OR post_content LIKE '%wp-content/uploads/20%.jpeg%'
       OR post_content LIKE '%wp-content/uploads/20%.png%'
""")

updated = 0
total_replacements = 0

for row in cur.fetchall():
    post_id = row['ID']
    content = row['post_content']
    
    new_content, count = pattern.subn(r'\1.webp', content)
    
    if count > 0:
        total_replacements += count
        updated += 1
        if not dry_run:
            conn.execute("UPDATE wp_posts SET post_content = ? WHERE ID = ?", (new_content, post_id))

if not dry_run:
    conn.commit()

prefix = "[DRY RUN] " if dry_run else ""
print(f"{prefix}Updated {updated} posts with {total_replacements} URL replacements")

# Also fix postmeta _wp_attached_file
cur.execute("""
    SELECT COUNT(*) as cnt FROM wp_postmeta 
    WHERE meta_key = '_wp_attached_file' 
    AND (meta_value LIKE '%.jpg' OR meta_value LIKE '%.jpeg' OR meta_value LIKE '%.png')
""")
meta_count = cur.fetchone()['cnt']
print(f"Postmeta _wp_attached_file with old extensions: {meta_count}")

if meta_count > 0 and not dry_run:
    for ext in ['jpg', 'jpeg', 'png']:
        conn.execute(f"""
            UPDATE wp_postmeta SET meta_value = 
            SUBSTR(meta_value, 1, LENGTH(meta_value) - {len(ext)}) || 'webp'
            WHERE meta_key = '_wp_attached_file' AND meta_value LIKE '%.{ext}'
        """)
    conn.commit()
    print(f"Fixed postmeta entries")

# Verify no external URLs were touched
cur.execute("SELECT COUNT(*) as cnt FROM wp_posts WHERE post_content LIKE '%imgur.com%.webp%'")
print(f"Verification - imgur.webp refs: {cur.fetchone()['cnt']}")
cur.execute("SELECT COUNT(*) as cnt FROM wp_posts WHERE post_content LIKE '%wikipedia.org%.webp%'")  
print(f"Verification - wikipedia.webp refs: {cur.fetchone()['cnt']}")

conn.close()
print("Done!")
