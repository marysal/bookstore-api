<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Throwable;

class JsonErrorController extends AbstractController
{
    public function show(Throwable $exception)
    {

        $error = [
            "error" => $exception->getMessage()
        ];

        return  $this->json($error);
    }
}