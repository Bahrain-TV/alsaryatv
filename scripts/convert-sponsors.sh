#!/usr/bin/env bash
set -euo pipefail

src_dir="public/images"
dst_dir="resources/images"
mkdir -p "${dst_dir}"

converter=""
if command -v rsvg-convert >/dev/null 2>&1; then
  converter="rsvg-convert"
elif command -v magick >/dev/null 2>&1; then
  converter="magick"
elif command -v convert >/dev/null 2>&1; then
  converter="convert"
elif command -v inkscape >/dev/null 2>&1; then
  converter="inkscape"
fi

if [ -z "$converter" ]; then
  echo "No SVG conversion tool found (rsvg-convert, magick/convert, or inkscape)." >&2
  exit 2
fi

echo "Using converter: $converter"

shopt -s nullglob
for svg in "$src_dir"/*.svg; do
  base=$(basename "$svg" .svg)
  png_src="$src_dir/$base.png"

  if [ -f "$png_src" ]; then
    echo "Skipping conversion, PNG already exists: $png_src"
  else
    echo "Converting: $svg -> $png_src"
    if [ "$converter" = "rsvg-convert" ]; then
      # Try with width 800 fallback to default
      if ! rsvg-convert -w 800 -o "$png_src" "$svg"; then
        rsvg-convert -o "$png_src" "$svg"
      fi
    elif [ "$converter" = "magick" ] || [ "$converter" = "convert" ]; then
      # magick/convert may use different invocation
      if command -v magick >/dev/null 2>&1 && [ "$converter" = "magick" ]; then
        magick convert "$svg" "$png_src"
      else
        convert "$svg" "$png_src"
      fi
    else
      # inkscape
      inkscape "$svg" --export-type=png --export-filename="$png_src"
    fi
  fi

  if [ -d "$dst_dir" ]; then
    if [ ! -f "$dst_dir/$base.png" ]; then
      cp "$png_src" "$dst_dir/"
      echo "Copied to resources: $dst_dir/$base.png"
    else
      echo "Resource destination already has: $dst_dir/$base.png"
    fi
  fi
done
