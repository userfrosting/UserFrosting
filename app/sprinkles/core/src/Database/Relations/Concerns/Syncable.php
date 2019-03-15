<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Relations\Concerns;

/**
 * Implements the `sync` method for HasMany relationships.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
trait Syncable
{
    /**
     * Synchronizes an array of data for related models with a parent model.
     *
     * @param mixed[] $data
     * @param bool    $deleting       Delete models from the database that are not represented in the input data.
     * @param bool    $forceCreate    Ignore mass assignment restrictions on child models.
     * @param string  $relatedKeyName The primary key used to determine which child models are new, updated, or deleted.
     */
    public function sync($data, $deleting = true, $forceCreate = false, $relatedKeyName = null)
    {
        $changes = [
            'created' => [], 'deleted' => [], 'updated' => [],
        ];

        if (is_null($relatedKeyName)) {
            $relatedKeyName = $this->related->getKeyName();
        }

        // First we need to attach any of the associated models that are not currently
        // in the child entity table. We'll spin through the given IDs, checking to see
        // if they exist in the array of current ones, and if not we will insert.
        $current = $this->newQuery()->pluck(
            $relatedKeyName
        )->all();

        // Separate the submitted data into "update" and "new"
        $updateRows = [];
        $newRows = [];
        foreach ($data as $row) {
            // We determine "updateable" rows as those whose $relatedKeyName (usually 'id') is set, not empty, and
            // match a related row in the database.
            if (isset($row[$relatedKeyName]) && !empty($row[$relatedKeyName]) && in_array($row[$relatedKeyName], $current)) {
                $id = $row[$relatedKeyName];
                $updateRows[$id] = $row;
            } else {
                $newRows[] = $row;
            }
        }

        // Next, we'll determine the rows in the database that aren't in the "update" list.
        // These rows will be scheduled for deletion.  Again, we determine based on the relatedKeyName (typically 'id').
        $updateIds = array_keys($updateRows);
        $deleteIds = [];
        foreach ($current as $currentId) {
            if (!in_array($currentId, $updateIds)) {
                $deleteIds[] = $currentId;
            }
        }

        // Delete any non-matching rows
        if ($deleting && count($deleteIds) > 0) {
            // Remove global scopes to avoid ambiguous keys
            $this->getRelated()
                 ->withoutGlobalScopes()
                 ->whereIn($relatedKeyName, $deleteIds)
                 ->delete();

            $changes['deleted'] = $this->castKeys($deleteIds);
        }

        // Update the updatable rows
        foreach ($updateRows as $id => $row) {
            // Remove global scopes to avoid ambiguous keys
            $this->getRelated()
                 ->withoutGlobalScopes()
                 ->where($relatedKeyName, $id)
                 ->update($row);
        }

        $changes['updated'] = $this->castKeys($updateIds);

        // Insert the new rows
        $newIds = [];
        foreach ($newRows as $row) {
            if ($forceCreate) {
                $newModel = $this->forceCreate($row);
            } else {
                $newModel = $this->create($row);
            }
            $newIds[] = $newModel->$relatedKeyName;
        }

        $changes['created'] = $this->castKeys($newIds);

        return $changes;
    }

    /**
     * Cast the given keys to integers if they are numeric and string otherwise.
     *
     * @param  array $keys
     * @return array
     */
    protected function castKeys(array $keys)
    {
        return (array) array_map(function ($v) {
            return $this->castKey($v);
        }, $keys);
    }

    /**
     * Cast the given key to an integer if it is numeric.
     *
     * @param  mixed $key
     * @return mixed
     */
    protected function castKey($key)
    {
        return is_numeric($key) ? (int) $key : (string) $key;
    }
}
