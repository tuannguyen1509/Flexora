<?php
namespace Flexora\Contracts\V1;

/** No writer or legacy storage implementation in Phase 03. */
interface ReadOnlyBindingV1 {
	/** @return LookupV1 Field ID is opaque; context is not authorization. */
	public function lookup( $field_id, ContextV1 $context );
}
