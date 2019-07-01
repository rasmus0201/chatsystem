<?php

namespace Bundsgaard\ChatSupport\Storage;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return config('chatsupport.storage.database.connection');
    }
}
