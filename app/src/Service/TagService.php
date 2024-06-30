<?php
/**
 * Tag service.
 */

namespace App\Service;

use App\Entity\Tag;
use App\Form\Type\TagType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class TagService.
 */
class TagService implements TagServiceInterface
{
    private EntityManagerInterface $entityManager;
    private CsrfTokenManagerInterface $csrfTokenManager;
    private FormFactoryInterface $formFactory;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface    $entityManager    Entity manager
     * @param CsrfTokenManagerInterface $csrfTokenManager CSRF token manager
     * @param FormFactoryInterface      $formFactory      Form factory
     */
    public function __construct(EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager, FormFactoryInterface $formFactory)
    {
        $this->entityManager = $entityManager;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->formFactory = $formFactory;
    }

    /**
     * Get all tags.
     *
     * @return Tag[]
     */
    public function getAllTags(): array
    {
        return $this->entityManager->getRepository(Tag::class)->findAll();
    }

    /**
     * Create a new tag.
     *
     * @param Request $request HTTP request
     *
     * @return Tag|null The created Tag entity if successful, or null if form submission failed
     */
    public function createTag(Request $request): ?Tag
    {
        return $this->processForm(new Tag(), $request);
    }

    /**
     * Update an existing tag.
     *
     * @param Request $request HTTP request
     * @param Tag     $tag     The tag entity
     *
     * @return Tag|null The updated Tag entity if successful, or null if form submission failed
     */
    public function updateTag(Request $request, Tag $tag): ?Tag
    {
        return $this->processForm($tag, $request);
    }

    /**
     * Delete a tag.
     *
     * @param Request $request HTTP request
     * @param Tag     $tag     The tag entity
     */
    public function deleteTag(Request $request, Tag $tag): void
    {
        if ($this->csrfTokenManager->isTokenValid(new CsrfToken('delete'.$tag->getId(), $request->request->get('_token')))) {
            $this->entityManager->remove($tag);
            $this->entityManager->flush();
        }
    }

    /**
     * Process form data.
     *
     * @param Tag     $tag     The tag entity
     * @param Request $request The HTTP request
     *
     * @return Tag|null
     */
    private function processForm(Tag $tag, Request $request): ?Tag
    {
        $form = $this->formFactory->create(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($tag);
            $this->entityManager->flush();

            return $tag;
        }

        return null;
    }
}
