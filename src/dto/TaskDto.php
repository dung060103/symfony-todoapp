<?php

namespace App\dto;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;
class TaskDto
{
    #[Groups(['get', 'create'])]
    #[OA\Property(type: 'string', maxLength: 255, example: 'Task 1')]
    public string $name;
    #[Groups(['get', 'create'])]
    #[OA\Property(type: 'string', example: 'Description 1')]
    public string $description;
    #[Groups(['completed', 'get'])]
    #[OA\Property(type: 'boolean', example: true)]
    public string $isCompleted;
}