@component('mail::message')
# Novo contato

<p>Contato: {{ $name }} <{{ $email }}></p>
<p>Telefone: {{ $cell }}</p>

<p>{{ $message }}</p>

<p>*Esse e-mail é enviado automaticamente através do sistema</p>

@endcomponent