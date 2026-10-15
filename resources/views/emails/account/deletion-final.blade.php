<x-mail::message>
# Bonjour {{ $firstName }},

Votre compte Stylward a été **définitivement supprimé**.

Toutes vos données personnelles, votre boutique, vos créations et vos images ont été effacées de nos serveurs.

Merci d'avoir utilisé notre service.

Vous pouvez toujours créer un nouveau compte si vous le souhaitez.

<x-mail::button :url="config('app.frontend_url') . '/register'">
Créer un nouveau compte
</x-mail::button>

L'équipe Stylward
</x-mail::message>
