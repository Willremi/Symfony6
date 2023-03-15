<?php

namespace App\Controller\Admin;

use App\Entity\Images;
use App\Entity\Products;
use App\Form\ProductsFormType;
use App\Repository\ProductsRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/produits', name: 'admin_products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProductsRepository $productsRepository): Response
    {
        $produits = $productsRepository->findAll();
        return $this->render('admin/products/index.html.twig', compact('produits'));
    }
    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = new Products();

        // Création d'un formulaire
        $productForm = $this->createForm(ProductsFormType::class, $product);

        // Traitement de la requête du formulaire
        $productForm->handleRequest($request);
        
        // Vérification du formulaire si il est soumis et validé
        if($productForm->isSubmitted() && $productForm->isValid()) {
            // Récupération des images
            $images = $productForm->get('images')->getData();
            
            foreach($images as $image) {
                // Définition du dossier de destination
                $folder = 'products';

                // Appel du service d'ajout
                $fichier = $pictureService->add($image, $folder, 300, 300);

                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }

            // Générer le slug
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);
            
            // Arrondi le prix
            // $prix = $product->getPrice()*100;
            // $product->setPrice($prix);

            // Stockage
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');

            // Redirection
            return $this->redirectToRoute('admin_products_index');
        }

        return $this->render('admin/products/add.html.twig', [
            'productForm' => $productForm->createView()
        ]);

        // return $this->renderForm('admin/products/add.html.twig', compact('productForm'));
        // ['productForm'=> $productForm]
    }
    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Products $product, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        // Vérification si l'utilisateur peut éditer avec le voter
        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product);
        
        // Division par 100 le prix
        // $prix = $product->getPrice()/100;
        // $product->setPrice($prix);

        // Création d'un formulaire
        $productForm = $this->createForm(ProductsFormType::class, $product);

        // Traitement de la requête du formulaire
        $productForm->handleRequest($request);
        
        // Vérification du formulaire si il est soumis et validé
        if($productForm->isSubmitted() && $productForm->isValid()) {
            // Récupération des images
            $images = $productForm->get('images')->getData();
            
            foreach($images as $image) {
                // Définition du dossier de destination
                $folder = 'products';

                // Appel du service d'ajout
                $fichier = $pictureService->add($image, $folder, 300, 300);

                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }

            // Générer le slug
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);
            
            // Arrondi le prix
            // $prix = $product->getPrice() * 100;
            // $product->setPrice($prix);

            // Stockage
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès');

            // Redirection
            return $this->redirectToRoute('admin_products_index');
        }

        return $this->render('admin/products/edit.html.twig', [
            'productForm' => $productForm->createView(), 
            'product' => $product
        ]);

        // return $this->renderForm('admin/products/edit.html.twig', compact('productForm'));
        // ['productForm'=> $productForm]
    }
    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Products $product): Response
    {
        // Vérification si l'utilisateur peut supprimer avec le voter
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);
        return $this->render('admin/products/index.html.twig');
    }
    #[Route('/suppression/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(Images $image, Request $request, EntityManagerInterface $em, PictureService $pictureService): JsonResponse
    {
        // Récupération du contenu de la requête
        $data = json_decode($request->getContent(), true);

        if($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])) {
            // Token csrf est valide
            // Récupération du nom de l'image
            $nom = $image->getName();

            if($pictureService->delete($nom, 'products', 300, 300)) {
                // Suppression de l'image de la BDD
                $em->remove($image);
                $em->flush();

                return new JsonResponse(['success' => true], 200);
            }
            // Suppression a échoué
            return new JsonResponse(['error' => 'Erreur de suppression'], 400);
        }

        return new JsonResponse(['error' => 'Token Invalide'], 400);
    }
}