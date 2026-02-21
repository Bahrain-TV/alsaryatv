import { chromium } from 'playwright';
import { spawn } from 'node:child_process';
import process from 'node:process';
import cliProgress from 'cli-progress';
import chalk from 'chalk';

const args = new Map();
for (let i = 2; i < process.argv.length; i += 2) {
    const key = process.argv[i];
    const value = process.argv[i + 1];
    if (!key || !value) {
        break;
    }
    args.set(key, value);
}

const url = args.get('--url') ?? 'http://localhost:8000/obs-overlay';
const outPath = args.get('--out') ?? 'storage/app/obs-overlay.mov';
const baseSeconds = Number(args.get('--seconds') ?? '65');
const fps = Number(args.get('--fps') ?? '50');

// Pre-warm: Wait for animation to stabilize (at least one full cycle ~25s + buffer)
const preWarmMs = 2800 + Math.random() * 1200; // 2.8-4s pre-warm for page load
const subtractMs = 2000 + Math.random() * 1000; // Subtract 2-3s randomly
const seconds = Math.max(baseSeconds - (subtractMs / 1000), 30); // Floor at 30s for full cycle

const width = 1920;
const height = 1080;
const frameCount = Math.max(1, Math.round(seconds * fps));
const frameDelayMs = 1000 / fps;

// For 50i interlaced: use field encoding with top field first
const ffmpegArgs = [
    '-y',
    '-f', 'image2pipe',
    '-r', String(fps),
    '-i', '-',
    '-c:v', 'prores_ks',
    '-profile:v', '4',
    '-pix_fmt', 'yuva444p10le',
    '-qscale:v', '1',
    '-vendor', 'ap10',
    '-an',
    '-flags', '+ilme+ildct',
    '-top', '1',
    outPath
];

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

const bar = new cliProgress.SingleBar({
    format: '{label} |{bar}| {percentage}% || {value}/{total} || {duration} | {eta}s',
    barCompleteChar: '█',
    barIncompleteChar: '░',
    hideCursor: true,
    stopOnComplete: true,
    barsize: 50
});

const run = async () => {
    console.log(chalk.bgCyan.black.bold('\n ═══════════════════════════════════════════════════════════════ '));
    console.log(chalk.bgCyan.black.bold(' 🎬  AlSarya TV OBS Overlay Recorder - 50i Interlaced ProRes 4444 '));
    console.log(chalk.bgCyan.black.bold(' ═══════════════════════════════════════════════════════════════ \n'));

    console.log(chalk.yellow('  📺 Configuration:'));
    console.log(chalk.magenta(`     ┌─ Resolution: `) + chalk.cyan.bold(`${width}×${height}`) + chalk.magenta(` px`));
    console.log(chalk.magenta(`     ├─ Duration:  `) + chalk.green.bold(`${seconds}s`) + chalk.magenta(` at ${fps} FPS (50i)`));
    console.log(chalk.magenta(`     ├─ Frames:    `) + chalk.cyan.bold(frameCount));
    console.log(chalk.magenta(`     ├─ URL:       `) + chalk.blue.underline(url));
    console.log(chalk.magenta(`     ├─ Interlaced:`) + chalk.yellow.bold(' Yes (Top Field First)'));
    console.log(chalk.magenta(`     └─ Output:    `) + chalk.green.bold(outPath));
    console.log('');

    const ffmpeg = spawn('ffmpeg', ffmpegArgs, { 
        stdio: ['pipe', 'pipe', 'pipe'],
        detached: true
    });

    let ffmpegOutput = '';
    let ffmpegErrors = '';

    ffmpeg.stdout.on('data', (data) => {
        ffmpegOutput += data.toString();
    });

    ffmpeg.stderr.on('data', (data) => {
        ffmpegErrors += data.toString();
    });

    ffmpeg.on('error', (err) => {
        console.log(chalk.bgRed.white.bold(' ✗ ERROR ') + ' ' + chalk.red(err.message) + '\n');
        process.exitCode = 1;
    });

    const browser = await chromium.launch();
    const page = await browser.newPage({
        viewport: { width, height },
        deviceScaleFactor: 1
    });

    console.log(chalk.blue.bold('  ⚡ Loading page...'));
    await page.goto(url, { waitUntil: 'networkidle' });
    await page.evaluate(() => {
        document.documentElement.style.background = 'transparent';
        document.body.style.background = 'transparent';
    });

    console.log(chalk.green.bold('  ✓ Page loaded successfully'));
    console.log(chalk.dim(`  ⏳ Pre-warming animation (${Math.round(preWarmMs / 1000)}s)...\n`));
    await page.waitForTimeout(preWarmMs);

    console.log(chalk.bgGreen.black.bold(' CAPTURING ') + chalk.greenBright(` Recording ${Math.round(seconds)}s of pre-warmed animation...\n`));
    bar.start(frameCount, 0, { label: chalk.bgMagenta.white.bold(' RECORDING '), duration: '0:00', eta: '...' });

    const startTime = Date.now();

    for (let i = 0; i < frameCount; i += 1) {
        const frameStart = performance.now();
        const frame = await page.screenshot({
            type: 'png',
            omitBackground: true
        });
        if (!ffmpeg.stdin.write(frame)) {
            await new Promise((resolve) => ffmpeg.stdin.once('drain', resolve));
        }
        const elapsed = performance.now() - frameStart;
        const remaining = frameDelayMs - elapsed;
        if (remaining > 0) {
            await sleep(remaining);
        }

        const totalElapsed = Math.floor((Date.now() - startTime) / 1000);
        const mins = Math.floor(totalElapsed / 60);
        const secs = totalElapsed % 60;
        const percentage = Math.round((i / frameCount) * 100);
        
        let labelColor = chalk.bgMagenta.white.bold;
        if (percentage > 66) {
            labelColor = chalk.bgCyan.black.bold;
        } else if (percentage > 33) {
            labelColor = chalk.bgBlue.white.bold;
        }
        
        bar.update(i + 1, {
            duration: `${mins}:${secs.toString().padStart(2, '0')}`,
            eta: Math.max(0, Math.round((frameCount - i - 1) / fps)).toString(),
            label: labelColor(` ${percentage}% `)
        });
    }

    bar.stop();
    console.log(chalk.greenBright('\n  ✓ Capture complete!\n'));

    console.log(chalk.bgYellow.black.bold(' ENCODING ') + chalk.yellowBright(' Finalizing video...\n'));
    ffmpeg.stdin.end();
    await browser.close();

    await new Promise((resolve, reject) => {
        ffmpeg.on('exit', (code) => {
            if (code === 0) {
                resolve();
            } else {
                reject(new Error(`FFmpeg exited with code ${code}`));
            }
        });
    });

    const fileSize = await import('node:fs/promises')
        .then(fs => fs.stat(outPath))
        .then(stat => {
            const mb = (stat.size / 1024 / 1024).toFixed(2);
            return `${mb} MB`;
        })
        .catch(() => 'unknown');

    console.log(chalk.green.bold('  ✓ Video exported successfully\n'));
    console.log(chalk.bgGreen.black.bold(' SUCCESS '));
    console.log(chalk.greenBright('  ┌─ File:   ') + chalk.cyan.bold(outPath));
    console.log(chalk.greenBright('  ├─ Size:   ') + chalk.yellow.bold(fileSize));
    console.log(chalk.greenBright('  ├─ Format: ') + chalk.magenta.bold('ProRes 4444 (RGBA)'));
    console.log(chalk.greenBright('  ├─ Codec:  ') + chalk.cyan.bold('ProRes with Alpha Channel'));
    console.log(chalk.greenBright('  ├─ Scan:   ') + chalk.yellow.bold('Interlaced (50i, TFF)'));
    console.log(chalk.greenBright('  └─ Ready for TV broadcast!\n'));
    console.log(chalk.bgGreen.black.bold(' 🎉 DONE! 🎉 ') + '\n');
};

run().catch((err) => {
    console.error(chalk.red('\n✗ Error:'), err.message);
    process.exitCode = 1;
});
