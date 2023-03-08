<?php

namespace App\Controller\Admin;

use App\Entity\Products;
use App\Form\ProductsFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/produits', name: 'admin_products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/products/index.html.twig');
    }
    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = new Products();

        // Création d'un formulaire
        $productForm = $this->createForm(ProductsFormType::class, $product);

        // Traitement de la requête du formulaire
        $productForm->handleRequest($request);
        
        // Vérification du formulaire si il est soumis et validé
        if($productForm->isSubmitted() && $productForm->isValid()) {
            // Générer le slug
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);
            
            // Arrondi le prix
            $prix = $product->getPrice()*100;
            $product->setPrice($prix);

            // Stockage
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');

            // Redirection
            return $this->redirectToRoute('admin_products_index');
        }

        // return $this->render('admin/products/add.html.twig', [
        //     'productForm' => $productForm->createView()
        // ]);

        return $this->renderForm('admin/products/add.html.twig', compact('productForm'));
        // ['productForm'=> $productForm]
    }
    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Products $product, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        // Vérification si l'utilisateur peut éditer avec le voter
        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product);
        
        // Division par 100 le prix
        $prix = $product->getPrice()/100;
            $product->setPrice($prix);

        // Création d'un formulaire
        $productForm = $this->createForm(ProductsFormType::class, $product);

        // Traitement de la requête du formulaire
        $productForm->handleRequest($request);
        
        // Vérification du formulaire si il est soumis et validé
        if($productForm->isSubmitted() && $productForm->isValid()) {
            // Générer le slug
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);
            
            // Arrondi le prix
            $prix = $product->getPrice() * 100;
            $product->setPrice($prix);

            // Stockage
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès');

            // Redirection
            return $this->redirectToRoute('admin_products_index');
        }

        // return $this->render('admin/products/edit.html.twig', [
        //     'productForm' => $productForm->createView()
        // ]);

        return $this->renderForm('admin/products/edit.html.twig', compact('productForm'));
        // ['productForm'=> $productForm]
    }
    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Products $product): Response
    {
        // Vérification si l'utilisateur peut supprimer avec le voter
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);
        return $this->render('admin/products/index.html.twig');
    }
}