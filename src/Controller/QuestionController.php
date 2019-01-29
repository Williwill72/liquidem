<?php

namespace App\Controller;

use App\Entity\Question;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AbstractController
{
    /**
     * @Route("/questions", name="question_list")
     */
    public function list()
    {
        //Ce repository nous permet de faire des SELECT
        $questionRepository = $this->getDoctrine()->getRepository(Question::class);

        //équivalent à SELECT * FROM questions WHERE status = 'debating'
        //ORDER BY supports DESC LIMIT 1000
        $questions = $questionRepository->findBy(
            ['status' => 'debating'], //WHERE
            ['supports' => 'DESC'],    //ORDER BY
            1000,                      //limit
            0                          //offset
        );

        //Fais un var dump puis un die
        //dd($questions);

        //Fais un var_dump et affiche le résultat dans le débugueur
        //dump($questions);

        return $this->render('question/list.html.twig', [
            'questions' => $questions
        ]);
    }
}
