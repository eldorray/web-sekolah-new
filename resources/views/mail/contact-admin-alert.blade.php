<x-mail::message>
# Pesan baru dari situs

**{{ $message->name }}** ({{ $message->email }}) menulis:

<x-mail::panel>
{{ $message->message }}
</x-mail::panel>

<x-mail::button :url="route('admin.contacts.index')">
Buka kotak masuk
</x-mail::button>
</x-mail::message>
