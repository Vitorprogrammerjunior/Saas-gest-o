<x-mail::message>
# Você foi convidado! 🎉

**{{ $inviterName }}** convidou você para o workspace **{{ $workspaceName }}** no TaskFlow.

<x-mail::button :url="$acceptUrl" color="primary">
Aceitar Convite
</x-mail::button>

Este convite expira em **{{ $expiresAt }}**.

Se você não esperava este convite, pode ignorar este email.

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
