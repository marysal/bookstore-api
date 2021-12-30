<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Throwable;

class JsonErrorController extends AbstractController
{
    public function show(Throwable $exception)
    {

        $error = [
            "message" => $exception->getMessage()
        ];

       //var_dump($this->json($error));

        return  $this->json($error);
    }
}