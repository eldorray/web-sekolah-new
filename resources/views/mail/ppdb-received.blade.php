<x-mail::message>
# Pendaftaran diterima

Terima kasih, pendaftaran atas nama **{{ $registration->full_name }}** sudah kami terima.

Nomor pendaftaran Anda:

<x-mail::panel>
{{ $registration->registration_number }}
</x-mail::panel>

Simpan nomor ini. Panitia memverifikasi berkas dalam dua hari kerja dan
menghubungi Anda di {{ $registration->parent_phone }}.

Terima kasih,<br>
{{ $school }}
</x-mail::message>
