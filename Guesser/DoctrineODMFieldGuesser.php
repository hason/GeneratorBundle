<?php

namespace Admingenerator\GeneratorBundle\Guesser;

use Admingenerator\GeneratorBundle\Exception\NotImplementedException;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerAware;

class DoctrineODMFieldGuesser extends ContainerAware
{
    private $documentManager;

    private $metadata;

    private $guessRequired;

    private $defaultRequired;

    private static $current_class;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    protected function getMetadatas($class = null)
    {
        if ($class) {
            self::$current_class = $class;
        }

        if (isset($this->metadata[self::$current_class]) || !$class) {
            return $this->metadata[self::$current_class];
        }

        if (!$this->documentManager->getConfiguration()->getMetadataDriverImpl()->isTransient($class)) {
            $this->metadata[$class] = $this->documentManager->getClassMetadata($class);
        }

        return $this->metadata[$class];
    }

    public function getAllFields($class)
    {
        $fields = array();

        $metadata = $this->getMetadatas($class);

        foreach ($metadata->fieldMappings as $fieldName => $fieldMetadatas) {
            if (!$metadata->hasAssociation($fieldName)) {
                $fields[] = $fieldName;
            }
        }

        return $fields;
    }


    /**
     * Find out the database type for given model field path.
     * 
     * @param  string $model        The starting model.
     * @param  string $fieldPath    The field path.
     * @return string               The leaf field's primary key.
     */
    public function getDbType($model, $fieldPath)
    {
        $resolved = $this->resolveRelatedField($model, $fieldPath);
        $class = $resolved['class'];
        $field = $resolved['field'];

        $metadata = $this->getMetadatas($class);

        if ($metadata->hasAssociation($field)) {
            if ($metadata->isSingleValuedAssociation($field)) {
                return 'document';
            } else {
                return 'collection';
            }
        }

        if ($this->getMetadatas()->hasField($field)) {
            $mapping = $this->getMetadatas()->getFieldMapping($field);

            return $mapping['type'];
        }

        return 'virtual';
    }

    public function getModelType($class, $fieldName)
    {
        $metadata = $this->getMetadatas($class);

        if ($metadata->hasAssociation($fieldName)) {
            return $metadata->getAssociationTargetClass($fieldName);
        }

        if ($metadata->hasField($fieldName)) {
            return $metadata->getTypeOfField($fieldName);
        }

        return 'virtual';
    }

    public function getSortType($dbType)
    {
        $alphabeticTypes = array(
            'id',
            'custom_id',
            'string',
            'text',
        );

        $numericTypes = array(
            'decimal',
            'float',
            'int',
            'integer',
            'int_id',
            'bigint',
            'smallint',
        );

        if (in_array($dbType, $alphabeticTypes)) {
            return 'alphabetic';
        }

        if (in_array($dbType, $numericTypes)) {
            return 'numeric';
        }

        return 'default';
    }

    public function getFormType($dbType, $columnName)
    {
        $formTypes = $this->container->getParameter('admingenerator.doctrineodm_form_types');

        if (array_key_exists($dbType, $formTypes)) {
            return $formTypes[$dbType];
        } elseif ('virtual' === $dbType) {
            return 'virtual_form';
        } else {
            throw new NotImplementedException(
                'The dbType "'.$dbType.'" is not yet implemented '
                .'(column "'.$columnName.'" in "'.self::$current_class.'")'
            );
        }
    }

    public function getFilterType($dbType, $columnName)
    {
        $filterTypes = $this->container->getParameter('admingenerator.doctrineodm_filter_types');

        if (array_key_exists($dbType, $filterTypes)) {
            return $filterTypes[$dbType];
        } elseif ('virtual' === $dbType) {
            return 'virtual_filter';
        }  else {
           throw new NotImplementedException(
               'The dbType "'.$dbType.'" is not yet implemented '
               .'(column "'.$columnName.'" in "'.self::$current_class.'")'
           );
       }
    }

    public function getFormOptions($formType, $dbType, $columnName)
    {
        if ('virtual' === $dbType) {
            return array();
        }
        
        if ('boolean' == $dbType && 
            (preg_match("#^choice#i", $formType) || preg_match("#choice$#i", $formType))) {
            return array(
                'choices' => array(
                   0 => $this->container->get('translator')->trans('boolean.no', array(), 'Admingenerator'),
                   1 => $this->container->get('translator')->trans('boolean.yes', array(), 'Admingenerator')
                ),
                'empty_value' => $this->container->get('translator')->trans('boolean.yes_or_no', array(), 'Admingenerator')
            );
        }

        if (preg_match("#^document#i", $formType) || preg_match("#document$#i", $formType)) {
            $mapping = $this->getMetadatas()->getFieldMapping($columnName);

            return array(
                'class'         => $mapping['targetDocument'],
                'multiple'      => false,
            );
        }

        if (preg_match("#^collection#i", $formType) || preg_match("#collection$#i", $formType)) {
            return array(
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
            );
        }

        // TODO: is this still needed? is this valid?
        if ('collection' === $dbType) {
            $mapping = $this->getMetadatas()->getFieldMapping($columnName);

            return array(
                'class' => isset($mapping['targetDocument']) ? $mapping['targetDocument'] : null
            );
        }

        return array('required' => $this->isRequired($columnName));
    }

    protected function isRequired($fieldName)
    {
        if (!isset($this->guessRequired) || !isset($this->defaultRequired)) {
            $this->guessRequired = $this->container->getParameter('admingenerator.guess_required');
            $this->defaultRequired = $this->container->getParameter('admingenerator.default_required');
        }

        if (!$this->guessRequired) {
            return $this->defaultRequired;
        }

        $metadata = $this->getMetadatas();

        $hasField = $metadata->hasField($fieldName);
        $hasAssociation = $metadata->hasAssociation($fieldName);
        $isSingleValAssoc = $metadata->isSingleValuedAssociation($fieldName);

        if ($hasField && (!$hasAssociation || $isSingleValAssoc)) {
            return !$metadata->isNullable($fieldName);
        }

        return false;
    }

    public function getFilterOptions($formType, $dbType, $ColumnName)
    {
        $options = array('required' => false);

        if ('boolean' == $dbType) {
            $options['choices'] = array(
               0 => $this->container->get('translator')
                        ->trans('boolean.no', array(), 'Admingenerator'),
               1 => $this->container->get('translator')
                        ->trans('boolean.yes', array(), 'Admingenerator'),
            );

            $options['empty_value'] = $this->container->get('translator')
                ->trans('boolean.yes_or_no', array(), 'Admingenerator');
        }

        if (preg_match("#^document#i", $formType) || preg_match("#document$#i", $formType)) {
            return array_merge(
                $this->getFormOptions($formType, $dbType, $ColumnName),
                $options
            );
        }

        if (preg_match("#^collection#i", $formType) || preg_match("#collection$#i", $formType)) {
            return array_merge(
                $this->getFormOptions($formType, $dbType, $ColumnName),
                $options
            );
        }

        return $options;
    }

    /**
     * Find the pk name
     */
    public function getModelPrimaryKeyName($class)
    {
        return $this->getMetadatas($class)->getIdentifier();
    }

    /**
     * Find out the primary key for given model field path.
     * 
     * @param  string $model        The starting model.
     * @param  string $fieldPath    The field path.
     * @return string               The leaf field's primary key.
     */
    public function getPrimaryKeyFor($model, $fieldPath)
    {
        $resolved = $this->resolveRelatedField($model, $fieldPath);
        $class = $resolved['class'];
        $field = $resolved['field'];

        $metadata = $this->getMetadatas($class);

        if ($metadata->hasAssociation($field)) {
            $class = $metadata->getAssociationTargetClass($field);
            return $this->getModelPrimaryKeyName($class);
        } else {
            // if the leaf node is not an association
            return null;
        }
    }

    /**
     * Resolve field path for given model to class and field name.
     * 
     * @param  string $model        The starting model.
     * @param  string $fieldPath    The field path.
     * @return array                An array containing field and class information.
     */
    private function resolveRelatedField($model, $fieldPath)
    {
        $path = explode('.', $fieldPath);
        $field = array_pop($path);
        $class = $model;

        foreach ($path as $part) {
            $metadata = $this->getMetadatas($class);

            if (!$metadata->hasAssociation($part)) {
                throw new \LogicException('Field "'.$part.'" for class "'.$class.'" is not an association.');
            }

            $class = $metadata->getAssociationTargetClass($part);
        }
        
        return array(
            'field' => $field,
            'class' => $class
        );
    }
}
