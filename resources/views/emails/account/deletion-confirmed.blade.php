<x-mail::message>
# Bonjour {{ $firstName }},

Votre demande a été confirmée.

Votre boutique est désormais **désactivée** et n'est plus accessible publiquement.

Vos données seront définitivement supprimées le **{{ $dueAt }}**.

## Vous avez changé d'avis ?

Pas de souci. Reconnectez-vous à votre compte avant cette date pour annuler la suppression. Tout sera restauré automatiquement.

<x-mail::button :url="config('app.frontend_url') . '/login'">
Me reconnecter
</x-mail::button>

À bientôt,<br>
L'équipe Stylward
</x-mail::message>
