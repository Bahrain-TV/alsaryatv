#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="${1:-artifacts/dashboard-audit}"
MANIFEST_PATH="${2:-$ROOT_DIR/manifest.json}"
OUT_VIDEO="${3:-$ROOT_DIR/dashboard-functionalities-tutorial.mp4}"
WORK_DIR="$ROOT_DIR/video-work"
TSV_PATH="$WORK_DIR/scenes.tsv"
CLIPS_LIST="$WORK_DIR/clips.txt"
FPS=30
SEGMENT_SECONDS=4

mkdir -p "$WORK_DIR"

if [[ ! -f "$MANIFEST_PATH" ]]; then
  echo "Manifest not found: $MANIFEST_PATH"
  exit 1
fi

EN_FONT="/System/Library/Fonts/Supplemental/Arial.ttf"
AR_FONT="/System/Library/Fonts/Supplemental/Geeza Pro.ttc"

if [[ ! -f "$EN_FONT" ]]; then
  EN_FONT="/System/Library/Fonts/Supplemental/Helvetica.ttc"
fi
if [[ ! -f "$AR_FONT" ]]; then
  AR_FONT="$EN_FONT"
fi

node - "$MANIFEST_PATH" "$TSV_PATH" <<'NODE'
const fs = require('node:fs');
const path = require('node:path');

const [manifestPath, tsvPath] = process.argv.slice(2);
const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
const scenes = (manifest.scenes || []).filter((scene) => scene.status === 'captured');

const rows = scenes.map((scene, index) => {
  const imagePath = path.resolve(scene.path);
  const en = (scene.titleEn || '').replace(/\s+/g, ' ').trim();
  const ar = (scene.titleAr || '').replace(/\s+/g, ' ').trim();
  return [String(index + 1), imagePath, en, ar].join('\t');
});

fs.writeFileSync(tsvPath, rows.join('\n'), 'utf8');
NODE

if [[ ! -s "$TSV_PATH" ]]; then
  echo "No captured scenes found in manifest."
  exit 1
fi

escape_drawtext() {
  local text="$1"
  text="${text//\\/\\\\}"
  text="${text//:/\\:}"
  text="${text//\'/\\\'}"
  text="${text//%/\\%}"
  echo "$text"
}

: > "$CLIPS_LIST"

while IFS=$'\t' read -r idx img en ar; do
  clip="$WORK_DIR/clip-$(printf '%02d' "$idx").mp4"
  en_esc="$(escape_drawtext "$en")"
  ar_esc="$(escape_drawtext "$ar")"

  ffmpeg -y \
    -loop 1 -t "$SEGMENT_SECONDS" -i "$img" \
    -vf "
      scale=1920:1080:force_original_aspect_ratio=decrease,
      pad=1920:1080:(ow-iw)/2:(oh-ih)/2,
      zoompan=z='min(zoom+0.0015,1.10)':d=${FPS}*${SEGMENT_SECONDS}:s=1920x1080,
      fps=${FPS},
      drawbox=x=0:y=0:w=iw:h=176:color=black@0.58:t=fill,
      drawtext=fontfile='${EN_FONT}':text='${en_esc}':x=56:y=44:fontsize=46:fontcolor=white:borderw=2:bordercolor=black,
      drawtext=fontfile='${AR_FONT}':text='${ar_esc}':text_shaping=1:x=w-tw-56:y=106:fontsize=48:fontcolor=0xFACC15:borderw=2:bordercolor=black
    " \
    -c:v libx264 -pix_fmt yuv420p -r "$FPS" -preset medium -crf 20 \
    "$clip" >/dev/null 2>&1

  echo "file '$(cd "$(dirname "$clip")" && pwd)/$(basename "$clip")'" >> "$CLIPS_LIST"
done < "$TSV_PATH"

ffmpeg -y -f concat -safe 0 -i "$CLIPS_LIST" -c copy "$OUT_VIDEO" >/dev/null 2>&1

echo "Video tutorial created: $OUT_VIDEO"
