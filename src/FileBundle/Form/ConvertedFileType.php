<?php

namespace FileBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @inheritdoc
 */
class ConvertedFileType extends AbstractType
{
    /**
     * L'extension du fichier courant.
     *
     * @var string $extension
     */
    protected $extension;

    /**
     * Les extensions des fichiers transcodés.
     *
     * @var array $convertedFileExtension
     */
    protected $convertedFileExtension;

    /**
     * ConvertedFileType constructor.
     *
     * @param string $extension
     * @param $convertedFileExtension
     */
    public function __construct ($extension, $convertedFileExtension)
    {
        $this->extension = $extension;
        $this->convertedFileExtension = $convertedFileExtension;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array(
            'MP4' => 'mp4',
            'MP3' => 'mp3',
            'AVI' => 'avi'
        );

        switch ($this->extension) {
            case 'avi':
                unset($choices['AVI']);
                break;
            case 'mp3':
                unset($choices['MP3']);
                break;
            case 'mp4':
                unset($choices['MP4']);
                break;
            default:
                break;
        }

        foreach ($this->convertedFileExtension as $extensions) {
            if (in_array($extensions, $choices)) {
                unset($choices[array_search($extensions, $choices)]);
            }
        }

        $builder
            ->add('format', ChoiceType::class, array(
                'choices' => $choices,
                'mapped' => false
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FileBundle\Entity\ConvertedFile'
        ));
    }

}