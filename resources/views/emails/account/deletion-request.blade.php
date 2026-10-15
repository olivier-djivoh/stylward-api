<x-mail::message>
# Bonjour {{ $firstName }},

Nous avons reçu une demande de suppression de votre compte **Stylward**.

Pour confirmer, cliquez sur le bouton ci-dessous. Votre boutique sera **désactivée immédiatement** et vos données conservées **30 jours**.

<x-mail::button :url="$link">
Confirmer la suppression
</x-mail::button>

Si vous n'êtes pas à l'origine de cette demande, **ignorez cet email** — votre compte ne sera pas supprimé.

Une fois confirmé, vous aurez jusqu'au **{{ $dueAt }}** pour annuler en vous reconnectant.

À bientôt,<br>
L'équipe Stylward
</x-mail::message>
