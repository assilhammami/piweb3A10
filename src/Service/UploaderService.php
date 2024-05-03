<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
class UploaderService
{
    
    public function __construct(private SluggerInterface $slugger)
    {
       
    }
    
    
    
    public function uploadFile(UploadedFile $file, string $targetDirectory)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move(
                $targetDirectory,
                $newFilename
            );
            } catch (FileException $e) {
            // Handle the exception (e.g., log an error message)
            // You may want to render an error message to the user
            throw $e;
        
        
    }
    return $newFilename;

}}