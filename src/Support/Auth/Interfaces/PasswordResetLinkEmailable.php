<?php

namespace Fantom\Support\Auth\Interfaces;

interface PasswordResetLinkEmailable
{
	public function sendPasswordResetLinkByEmail(array $data): bool;
}