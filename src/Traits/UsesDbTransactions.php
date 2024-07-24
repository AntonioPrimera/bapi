<?php

namespace AntonioPrimera\Bapi\Traits;

trait UsesDbTransactions
{
	protected bool $useDbTransaction = true;
	
	protected function setDbTransaction(bool $useDbTransaction): static
	{
		$this->useDbTransaction = $useDbTransaction;
		return $this;
	}
}