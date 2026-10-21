const { spawn } = require('node:child_process');
const fs = require('node:fs');
const path = require('node:path');
const { pathToFileURL } = require('node:url');
const root = process.cwd();
const target = 'app/Actions/Activities/PublishActivity.php';
const uri = pathToFileURL(path.join(root, target)).href;
const server = spawn(process.execPath, [
    'C:/Users/User/.vscode/extensions/bmewburn.vscode-intelephense-client-1.18.5/node_modules/intelephense/lib/intelephense.js',
    '--stdio',
]);
let buffer = Buffer.alloc(0);
let nextId = 0;
const requests = new Map();
function send(message) {
    const json = JSON.stringify(message);
    server.stdin.write(`Content-Length: ${Buffer.byteLength(json)}\r\n\r\n${json}`);
}
function request(method, params) {
    const id = ++nextId;
    send({ jsonrpc: '2.0', id, method, params });
    return new Promise(resolve => requests.set(id, resolve));
}
function notify(method, params) { send({ jsonrpc: '2.0', method, params }); }
server.stdout.on('data', chunk => {
    buffer = Buffer.concat([buffer, chunk]);
    while (true) {
        const end = buffer.indexOf('\r\n\r\n');
        if (end < 0) break;
        const size = Number(/Content-Length: (\d+)/i.exec(buffer.subarray(0, end).toString())[1]);
        if (buffer.length < end + 4 + size) break;
        const message = JSON.parse(buffer.subarray(end + 4, end + 4 + size));
        buffer = buffer.subarray(end + 4 + size);
        if (requests.has(message.id)) {
            requests.get(message.id)(message.result);
            requests.delete(message.id);
        } else if (message.method === 'workspace/configuration') {
            send({ jsonrpc: '2.0', id: message.id, result: message.params.items.map(item => item.section === 'intelephense' ? { environment: { phpVersion: '8.3.0' } } : null) });
        } else if (message.id) {
            send({ jsonrpc: '2.0', id: message.id, result: null });
        } else if (message.method === 'textDocument/publishDiagnostics') {
            console.log(JSON.stringify(message.params));
        } else if (message.method === 'window/logMessage' || message.method === 'window/showMessage') {
            console.log(JSON.stringify(message.params));
        }
    }
});
server.stderr.on('data', chunk => process.stderr.write(chunk));
(async () => {
    await request('initialize', {
        processId: process.pid,
        rootUri: pathToFileURL(root).href,
        capabilities: { workspace: { configuration: true } },
        initializationOptions: { storagePath: path.join(root, '.intelephense-tmp') },
    });
    notify('initialized', {});
    notify('textDocument/didOpen', { textDocument: { uri, languageId: 'php', version: 1, text: fs.readFileSync(path.join(root, target), 'utf8') } });
    setTimeout(() => { server.kill(); }, 150000);
})();
