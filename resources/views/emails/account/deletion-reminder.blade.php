<x-mail::message>
# Bonjour {{ $firstName }},

@if ($daysLeft === 1)
**Dernier jour.** Votre compte sera supprimé demain, le **{{ $dueAt }}**.
@else
Votre compte sera supprimé dans **{{ $daysLeft }} jours**, le **{{ $dueAt }}**.
@endif

Si vous souhaitez conserver votre boutique et vos créations, il vous suffit de vous reconnecter.

<x-mail::button :url="config('app.frontend_url') . '/login'">
Me reconnecter
</x-mail::button>

Après cette date, la suppression sera **définitive et irréversible**.

L'équipe SaaS Stylistes
</x-mail::message>
