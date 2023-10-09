<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\Factory\PagerfantaFactory;
use JMS\Serializer\SerializerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Hateoas\Configuration\Route as HateoasRoute;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class ProductController extends AbstractController
{
    /**
     * This method displays all products
     *
     * @OA\Response(
     *     response=200,
     *     description="Displays products list",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class))
     *     )
     * )
     * @OA\Tag(name="Products")
     *
     * @param ProductRepository      $productRepository
     * @param SerializerInterface    $serializer
     * @param TagAwareCacheInterface $cache
     * @param Request                $request
     *
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route('/bilemo/products', name: 'app_products', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour consulter les produits')]
    public function getAllProducts(
        ProductRepository $productRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
        Request $request
    ): JsonResponse {
        $page = $request->get('page', 1);
        $adapter = new ArrayAdapter($productRepository->findAll());
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage($adapter, $page, 5);
        $idCache = 'getAllProducts-';


        $jsonProductList = $cache->get(
            $idCache,
            function (ItemInterface $item) use ($pager, $serializer) {
                $pagerFanta = new PagerfantaFactory();

                echo('L\'élément n\'est pas encore en cache !');
                $item->tag('productsCache')
                    ->expiresAfter(60);

                $productList
                    = $pagerFanta->createRepresentation(
                        $pager,
                        new HateoasRoute('app_products', array(), true),
                        new CollectionRepresentation(
                            $pager->getCurrentPageResults()
                        )
                    );


                return $serializer->serialize($productList, 'json');
            }
        );

        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    /**
     * This method displays a single product details
     *
     * @OA\Response(
     *     response=200,
     *     description="Displays single product details",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class))
     *     )
     * )
     * @OA\Tag(name="Products")
     *
     * @param Product             $product
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse
     */
    #[Route('/bilemo/products/{id}', name: 'app_products_details', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour consulter un produit')]
    public function getProductDetails(
        Product $product,
        SerializerInterface $serializer
    ): JsonResponse {
        $jsonProduct = $serializer->serialize($product, 'json');

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
