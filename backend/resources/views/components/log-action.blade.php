@props(['action'])
@php
    $color = match ($action) {
        'created', 'registered', 'saved' => 'green',
        'updated', 'password_changed', 'password_reset', 'email_verified', 'verification_resent' => 'blue',
        'deleted', 'login_failed', 'login_denied' => 'red',
        'restored' => 'amber',
        'login', 'logout' => 'sky',
        'downloaded' => 'violet',
        default => 'gray',
    };
@endphp
<x-badge :color="$color" {{ $attributes }}>{{ ucfirst(str_replace('_', ' ', $action)) }}</x-badge>
