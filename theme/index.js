#!/usr/bin/env node

// Simple script to build a .scss file to .css
// Using postcss for autoprefixing and minifying
const { promisify } = require('util');
const fs = require('fs');
const path = require('path');

const sass = require('sass');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const postcss = require('postcss');
const writeFile = promisify(fs.writeFile);

const THEME_DIR = __dirname;
const ANAX_DIR = path.join(__dirname, '../');
const INPUT = `${THEME_DIR}/theme.scss`;
const OUTPUT = `${ANAX_DIR}/htdocs/css/theme.min.css`;
const POSTCSS_PLUGINS = [ autoprefixer, cssnano ];
const POSTCSS_OPTS = {
  from: INPUT,
  to: OUTPUT,
  map: { inline: false },
};
const SASS_OPTS = {
  file: INPUT,
  includePaths: [ `${ANAX_DIR}/node_modules` ],
};


function print(msg) {
  process.stdout.clearLine();
  process.stdout.cursorTo(0);
  process.stdout.write(msg);
}


async function renderFile() {
  print('Rebuilding theme...');

  try {
    // First process SCSS then run PostCSS
    const { css } = sass.renderSync(SASS_OPTS);
    const res = await postcss(POSTCSS_PLUGINS).process('' + css, POSTCSS_OPTS);

    res.warnings().forEach(warn => console.warn('' + warn));
    await writeFile(OUTPUT, res.css, 'utf8');

    if (res.map) {
      await writeFile(`${OUTPUT}.map`, res.map, 'utf8');
    }

    print('Theme updated');
  } catch (ex) {
    print(`Error: ${ex.message}`);
  }
}


function watchRecursive(dirpath, callback) {
  const files = fs.readdirSync(dirpath);

  for (let file of files) {
    const filepath = path.join(dirpath, file);
    const stat = fs.statSync(filepath);

    if (stat.isDirectory()) {
      watchRecursive(filepath, callback);
    }
  }

  // Watch directory
  fs.watch(dirpath, callback);
}


async function main() {
  const argv = process.argv.slice(2);
  const mode = argv[0] || 'build';

  switch(mode) {
    case 'watch':
      // We want fallthrough to build
      watchRecursive(THEME_DIR, renderFile);

    case 'build':
      await renderFile();
      break;

    default:
      console.error('Build mode invalid!');
      process.exit(1);
  }
}

main().catch(console.error);

