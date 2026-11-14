# Fonts

The theme uses **Switzer** (variable, 100–900) by Indian Type Foundry, licensed under the
[ITF Free Font License](https://www.fontshare.com/licenses/itf-ffl): free for commercial use and
self-hosting on your own website, but the font files may not be redistributed (e.g. in a public repository).
That is why the files are not in Git.

Setup (local and on the server):

1. Download Switzer from https://www.fontshare.com/fonts/switzer
2. Copy `Fonts/WEB/fonts/Switzer-Variable.woff2` to `assets/fonts/switzer/Switzer-Variable.woff2`
   (and the license `License/FFL.txt` next to it).

Without the file the site falls back to the system UI font (inc/assets.php skips the preload).
The deploy zip must include `assets/fonts/switzer/` (see README.md → Deploy).
