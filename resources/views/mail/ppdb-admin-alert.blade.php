<x-mail::message>
# Pendaftar baru

**{{ $registration->full_name }}** mendaftar untuk {{ $registration->grade_target }}.

- Nomor: {{ $registration->registration_number }}
- Asal sekolah: {{ $registration->previous_school }}
- Orang tua: {{ $registration->father_name }} / {{ $registration->mother_name }}
- Kontak: {{ $registration->parent_phone }} · {{ $registration->parent_email }}

<x-mail::button :url="route('admin.ppdb.show', $registration->registration_number)">
Buka pendaftar
</x-mail::button>
</x-mail::message>
