<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\Pelicula;
use App\Form\PeliculaType;


class ClienteController extends AbstractController
{
    private $urlApi = "http://127.0.0.1:8000/api/";
    /**
     * @Route("/", name="lista_peliculas")
     */
    public function index()
    {
        $client = HttpClient::create();
        $response = $client->request('GET', $this->urlApi.'peliculas');

        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
        return $this->render('cliente/index.html.twig', [
            'peliculas' => $content,
        ]);
    }

    /**
     * @Route("/pelicula/aniadir/", name="pelicula_aniadir")
     */
    public function peliculaAniadir(Request $request)
    {

        $pelicula = new Pelicula();


        $form = $this->createForm(PeliculaType::class, $pelicula);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $pelicula = $form->getData();

            $client = HttpClient::create();
            $response = $client->request('POST', $this->urlApi.'pelicula',[
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => ['nombre' => $pelicula->getNombre(),
                            'genero' => $pelicula->getGenero(),
                            'descripcion' => $pelicula->getDescripcion()
                ],
            ]);

            return $this->redirectToRoute('lista_peliculas');
        }



        return $this->render('cliente/formPelicula.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/pelicula/modificar/{id}", name="pelicula_modificar")
     */
    public function peliculaModificar($id,Request $request)
    {
        $client = HttpClient::create();
        $response = $client->request('GET', $this->urlApi.'pelicula/'.$id);

        $content = $response->toArray();

        $pelicula = new Pelicula();

        $pelicula->setNombre($content['nombre']);
        $pelicula->setGenero($content['genero']);
        $pelicula->setDescripcion($content['descripcion']);


        $form = $this->createForm(PeliculaType::class, $pelicula);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $pelicula = $form->getData();

            $client = HttpClient::create();
            $response = $client->request('PUT', $this->urlApi.'pelicula/'.$content['id'],[
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => ['nombre' => $pelicula->getNombre(),
                            'genero' => $pelicula->getGenero(),
                            'descripcion' => $pelicula->getDescripcion()
                ],
            ]);

            return $this->redirectToRoute('lista_peliculas');
        }



        return $this->render('cliente/formPelicula.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/pelicula/{id}", name="pelicula_detalle")
     */
    public function peliculaDetalle($id)
    {
        $client = HttpClient::create();
        $response = $client->request('GET', $this->urlApi.'pelicula/'.$id);

        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
        return $this->render('cliente/detallePelicula.html.twig', [
            'pelicula' => $content,
        ]);
    }

    /**
     * @Route("/pelicula/borrar/{id}", name="pelicula_borrar")
     */
    public function peliculaBorrar($id)
    {
        $client = HttpClient::create();
        $response = $client->request('DELETE', $this->urlApi.'pelicula/'.$id);

        $statusCode = $response->getStatusCode();
        if($statusCode == 200){

            $response = $client->request('GET', $this->urlApi.'peliculas');
            $content = $response->toArray();
            return $this->render('cliente/index.html.twig', [
                'peliculas' => $content,
            ]);
        }else{

            $response = $client->request('GET', $this->urlApi.'pelicula/'.$id);
            $content = $response->toArray();
            // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
            return $this->render('cliente/detallePelicula.html.twig', [
                'pelicula' => $content,
            ]);

        } 
    }
}
