<?php

namespace App\Models\Concerns;

trait SecureOperations
{
    /**
     * Perform secure delete operations
     *
     * @return bool
     */
    public function secureDelete()
    {
        if ($this->canBeDeleted()) {
            return $this->delete();
        }

        return false;
    }

    /**
     * Check if the model can be deleted securely
     *
     * @return bool
     */
    protected function canBeDeleted()
    {
        // Implement your security logic here
        return true;
    }
}
