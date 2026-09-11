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
    'resources/js/pages/Dashboard.vue',
    'resources/js/components/AppSidebar.vue',
];

for (const file of files) {
    const fullPath = path.resolve(process.cwd(), file);
    if (!fs.existsSync(fullPath)) continue;
    let content = fs.readFileSync(fullPath, 'utf8');

    // Replace basic resources
    const resources = ['businesses', 'services', 'staff', 'subscriptions'];
    for (const res of resources) {
        // route('res.index') -> '/res'
        content = content.replace(
            new RegExp(`route\\('${res}\\.index'\\)`, 'g'),
            `\`/${res}\``,
        );
        // route('res.create') -> '/res/create'
        content = content.replace(
            new RegExp(`route\\('${res}\\.create'\\)`, 'g'),
            `\`/${res}/create\``,
        );
        // route('res.store') -> '/res'
        content = content.replace(
            new RegExp(`route\\('${res}\\.store'\\)`, 'g'),
            `\`/${res}\``,
        );
        // route('res.update', id) -> '/res/id'
        content = content.replace(
            new RegExp(`route\\('${res}\\.update',\\s*(.+?)\\)`, 'g'),
            `\`/${res}/\${$1}\``,
        );
        // route('res.destroy', id) -> '/res/id'
        content = content.replace(
            new RegExp(`route\\('${res}\\.destroy',\\s*(.+?)\\)`, 'g'),
            `\`/${res}/\${$1}\``,
        );
        // route('res.edit', id) -> '/res/id/edit'
        content = content.replace(
            new RegExp(`route\\('${res}\\.edit',\\s*(.+?)\\)`, 'g'),
            `\`/${res}/\${$1}/edit\``,
        );
        // route('res.show', id) -> '/res/id'
        content = content.replace(
            new RegExp(`route\\('${res}\\.show',\\s*(.+?)\\)`, 'g'),
            `\`/${res}/\${$1}\``,
        );
    }

    // Subscriptions special routes
    // route('subscriptions.renewForm', id) -> `/subscriptions/${id}/renew`
    content = content.replace(
        new RegExp(`route\\('subscriptions\\.renewForm',\\s*(.+?)\\)`, 'g'),
        `\`/subscriptions/\${$1}/renew\``,
    );
    // route('subscriptions.renew', id) -> `/subscriptions/${id}/renew`
    content = content.replace(
        new RegExp(`route\\('subscriptions\\.renew',\\s*(.+?)\\)`, 'g'),
        `\`/subscriptions/\${$1}/renew\``,
    );

    // Subscriptions create with query param: route('subscriptions.create', { business_id: id })
    // We can just regex this specific one manually since it's only in Businesses/Show.vue
    content = content.replace(
        /route\('subscriptions\.create',\s*\{\s*business_id:\s*(.+?)\s*\}\)/g,
        `\`/subscriptions/create?business_id=\${$1}\``,
    );

    // Dashboard
    content = content.replace(/route\('dashboard'\)/g, `'/dashboard'`);

    fs.writeFileSync(fullPath, content, 'utf8');
}
console.log('Routes replaced successfully.');
