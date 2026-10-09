import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

// Use Laravel's locale catalog in the standalone client too.
const site = JSON.parse(execFileSync(process.env.SAOLA_PHP_BINARY || 'php', [
    '-r', 'echo json_encode(require $argv[1]);', fileURLToPath(new URL('./config/site.php', import.meta.url)),
], { encoding: 'utf8' }));

export default { contexts: { web: { view: { systemData: { __site__: { locales: site.locales } } } } } };
