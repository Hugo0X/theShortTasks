<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Entity\Pin;
use App\Repository\PinRepository;

/**
* @Route("/task")
*/
class TaskController extends AbstractController
{
    /**
     * @Route("/", name="task")
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    /**
     * @Route("/add/{id<[0-9]+>}", name="app_add_task", methods={"POST"})
     */
    public function addTask(Request $request, Pin $pin, TaskRepository $taskRepository): Response
    {
        if($this->isCsrfTokenValid('pin_task_new_' . $pin->getId(), $request->request->get('csrf_token')) && intval($request->request->get('id')))
        { 
            $task = new task;
            $task->setName($request->request->get('taskName'));
            $task->setStatus($request->request->get('taskStatus'));
            $task->setIsEnded(false);
            $task->setPin($pin);

            $this->em->persist($task);
            $this->em->flush();

            $this->addFlash('success', 'Recording completed!');
        }

        return $this->redirectToRoute('app_pins_show', ['id' => $pin->getId()]);
    }

    /**
     * @Route("/ended/{id}", name="app_task_ended", methods={"POST"})
     */
    public function EndTask(Request $request, Pin $pin, TaskRepository $taskRepository): Response
    {
        if($this->isCsrfTokenValid('task_ended_' . $pin->getId(), $request->request->get('csrf_token')) && intval($request->request->get('id')))
        { 
            $task = $taskRepository->findOneBy(['id' => intval($request->request->get('taskId'))]);

            $task->setIsEnded(!$task->getIsEnded());
            $task->setPin($pin);

            $this->em->persist($task);
            $this->em->flush();

            $this->addFlash('success', 'Recording completed!');
        }

        return $this->redirectToRoute('app_pins_show', ['id' => $pin->getId()]);
    }

     /**
     * @Route("/delete/{id}", name="app_task_delete", methods={"DELETE"})
     */
    public function deleteTask(Request $request, Pin $pin, TaskRepository $taskRepository): Response
    {
        if ($this->isCsrfTokenValid('task_deletion_' . $pin->getId(), $request->request->get('csrf_token')) && intval($request->request->get('id')))
        {
            $task = $taskRepository->findOneBy(['id' => intval($request->request->get('taskId'))]);

            $this->em->remove($task);
            $this->em->flush();

            $this->addFlash('success', 'The task has been successfully deleted!');
        }

        return $this->redirectToRoute('app_pins_show', ['id' => $pin->getId()]);
    }

}
