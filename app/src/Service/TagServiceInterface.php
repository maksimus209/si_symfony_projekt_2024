<?php
/**
 * Tag service interface.
 */

namespace App\Service;

use App\Entity\Tag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface TagServiceInterface.
 */
interface TagServiceInterface
{
    /**
     * Get all tags.
     *
     * @return array List of tags
     */
    public function getAllTags(): array;

    /**
     * Create new tag.
     *
     * @param Request $request HTTP request
     *
     * @return Tag|null Created tag or null if form is not valid
     */
    public function createTag(Request $request): ?Tag;

    /**
     * Update tag.
     *
     * @param Request $request HTTP request
     * @param Tag     $tag     Tag entity
     *
     * @return Tag|null Updated tag or null if form is not valid
     */
    public function updateTag(Request $request, Tag $tag): ?Tag;

    /**
     * Delete tag.
     *
     * @param Request $request HTTP request
     * @param Tag     $tag     Tag entity
     */
    public function deleteTag(Request $request, Tag $tag): void;
}
