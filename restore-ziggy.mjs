import fs from 'fs';
import path from 'path';

const files = [
    'resources/js/pages/Businesses/Form.vue',
    'resources/js/pages/Businesses/Index.vue',
    'resources/js/pages/Businesses/Show.vue',
    'resources/js/pages/Services/Form.vue',
    'resources/js/pages/Services/Index.vue',
    'resources/js/pages/Services/Show.vue',
    'resources/js/pages/Staff/Form.vue',
    'resources/js/pages/Staff/Index.vue',
    'resources/js/pages/Subscriptions/Form.vue',
];

for (const file of files) {
    const fullPath = path.resolve(process.cwd(), file);
    if (!fs.existsSync(fullPath)) continue;
    let content = fs.readFileSync(fullPath, 'utf8');

    if (!content.includes(`import { route } from 'ziggy-js'`)) {
        content = content.replace(
            /<script setup lang="ts">\n/,
            `<script setup lang="ts">\nimport { route } from 'ziggy-js';\n`,
        );
    }

    // Replace all literal urls back to route calls
    // Note: I only need to handle ones I changed!
    // /businesses/create
    content = content.replace(
        /`\/(businesses|services|staff|subscriptions)\/create`/g,
        `route('$1.create')`,
    );

    // /businesses/${id}/edit
    content = content.replace(
        /`\/(businesses|services|staff|subscriptions)\/\$\{(.+?)\}\/edit`/g,
        `route('$1.edit', $2)`,
    );

    // /subscriptions/${id}/renew
    content = content.replace(
        /`\/subscriptions\/\$\{(.+?)\}\/renew`/g,
        `route('subscriptions.renewForm', $1)`,
    );

    // /businesses/${id} -> route('businesses.show', id) or route('businesses.destroy', id)
    // Actually, I can't blindly replace `/businesses/${id}` because it could be show, update, destroy.
    // Let's replace manually for those in the script by matching form actions and Link hrefs!

    content = content.replace(
        /form\.put\(`\/(businesses|services|staff|subscriptions)\/\$\{(.+?)\}`\)/g,
        `form.put(route('$1.update', $2))`,
    );
    content = content.replace(
        /form\.post\(`\/(businesses|services|staff|subscriptions)`\)/g,
        `form.post(route('$1.store'))`,
    );
    content = content.replace(
        /deleteForm\.delete\(`\/(businesses|services|staff|subscriptions)\/\$\{(.+?)\}`\)/g,
        `deleteForm.delete(route('$1.destroy', $2))`,
    );

    // Links to index
    content = content.replace(
        /href="`\/(businesses|services|staff|subscriptions)`"/g,
        `href="route('$1.index')"`,
    );
    content = content.replace(
        /href="?\/(businesses|services|staff|subscriptions)"?/g,
        `href="route('$1.index')"`,
    );

    // Links to show
    content = content.replace(
        /href="`\/(businesses|services|staff|subscriptions)\/\$\{(.+?)\}`"/g,
        `href="route('$1.show', $2)"`,
    );

    // Index view get
    content = content.replace(
        /router\.get\(`\/(businesses|services|staff|subscriptions)`/g,
        `router.get(route('$1.index')`,
    );

    // query param special case
    content = content.replace(
        /`\/subscriptions\/create\?business_id=\$\{(.+?)\}`/g,
        `route('subscriptions.create', { business_id: $1 })`,
    );

    // add missing `:` to hrefs that now have `route()`
    content = content.replace(/href="route/g, `:href="route`);

    fs.writeFileSync(fullPath, content, 'utf8');
}
console.log('Restored ziggy routes in Vue components.');
