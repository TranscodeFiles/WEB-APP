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
     * ConvertedFileType constructor.
     * 
     * @param string $extension
     */
    public function __construct ($extension)
    {
        $this->extension = $extension;
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