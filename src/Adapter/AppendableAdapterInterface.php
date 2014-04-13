<?php

namespace GuzzleHttp\Adapter;

/**
 * Adapter interface used to append a request to an ongoing transfer context.
 */
interface AppendableAdapterInterface
{
    /**
     * Appends transactions to an ongoing request batch.
     *
     * @param \Iterator $transactions Iterator that yields TransactionInterface
     */
    public function appendRequests(\Iterator $transactions);
}
