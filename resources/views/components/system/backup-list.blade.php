@props(['backups', 'integrity', 'canDownload' => false, 'canDelete' => false])

<div class="w-full max-w-full overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-stone-200">
            <thead class="bg-stone-50">
                <tr>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-extrabold uppercase text-stone-500">Backup</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-extrabold uppercase text-stone-500">Status</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-extrabold uppercase text-stone-500">Size</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-extrabold uppercase text-stone-500">Created</th>
                    <th scope="col" class="px-5 py-3 text-right text-xs font-extrabold uppercase text-stone-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200">
                @foreach ($backups as $backup)
                    <x-system.backup-row :backup="$backup" :integrity="$integrity[$backup->id]" :can-download="$canDownload" :can-delete="$canDelete" />
                @endforeach
            </tbody>
        </table>
    </div>
</div>
