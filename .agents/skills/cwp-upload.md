---
name: cwp-upload
description: Handle file or image upload in Laravel on CWP shared hosting. Use when asked to create upload feature, fix upload error, store image, or handle multipart form.
---

# Skill: File Upload (CWP Safe)

## Storage Rule
- Target directory: `public/uploads/`
- NEVER use: `Storage::disk('public')`, `storage/app/public`, `storage:link`

## Upload Controller Pattern
```php
public function store(Request $request)
{
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    $file     = $request->file('image');
    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

    $file->move(public_path('uploads'), $filename);

    $record->image = 'uploads/' . $filename;
    $record->save();
}
```

## Generate URL
```php
// PHP
asset($record->image)

// Blade
<img src="{{ asset($record->image) }}" alt="">

// With null safety
<img src="{{ $record->image ? asset($record->image) : asset('images/placeholder.jpg') }}">
```

## Subdirectory by Month (Recommended)
```php
$folder          = 'uploads/' . date('Y/m');
$destinationPath = public_path($folder);

if (!file_exists($destinationPath)) {
    mkdir($destinationPath, 0755, true);
}

$file->move($destinationPath, $filename);
$record->image = $folder . '/' . $filename;
```

## Checklist Before Writing Code
- [ ] `public/uploads/` exists in project (commit `.gitkeep`)
- [ ] Form uses `enctype="multipart/form-data"` and `method="POST"`
- [ ] Validation includes `mimes` and `max` size
- [ ] Permission `public/uploads/` = 755 on server (set via CWP File Manager)
