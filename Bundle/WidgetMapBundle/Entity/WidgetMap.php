<?php

namespace Victoire\Bundle\WidgetMapBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * @ORM\Table("vic_widget_map")
 * @ORM\Entity()
 */
class WidgetMap
{
    const ACTION_CREATE = 'create';
    const ACTION_OVERWRITE = 'overwrite';
    const ACTION_DELETE = 'delete';

    const POSITION_BEFORE = 'before';
    const POSITION_AFTER = 'after';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=255)
     */
    protected $action = null;

    /**
     * @var View
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\CoreBundle\Entity\View", inversedBy="widgetMaps")
     * @ORM\JoinColumn(name="view_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $view;

    /**
     * A WidgetMap has a View but also a contextualView (not persisted).
     * This contextualView is set when WidgetMap is build.
     * When getChilds and getSubstitutes are called, we use this contextualView to retrieve
     * concerned WidgetMaps in order to avoid useless Doctrine queries.
     *
     * @var View
     */
    protected $contextualView;

    /**
     * @var [Widget]
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\WidgetBundle\Entity\Widget", mappedBy="widgetMap", orphanRemoval=true, cascade={"persist", "remove"})
     */
    protected $widgets;

    /**
     * @deprecated
     *
     * @var Widget
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\WidgetBundle\Entity\Widget")
     * @ORM\JoinColumn(name="widget_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $widget;

    /**
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap", inversedBy="substitutes")
     * @ORM\JoinColumn(name="replaced_id", referencedColumnName="id")
     */
    protected $replaced;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap", mappedBy="replaced")
     */
    protected $substitutes;

    /**
     * @var string
     *
     * @ORM\Column(name="asynchronous", type="boolean")
     */
    protected $asynchronous = false;

    /**
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", nullable=true)
     */
    protected $position;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap", mappedBy="parent")
     */
    protected $children;

    /**
     * @var string
     *
     * @ORM\Column(name="slot", type="string", length=255, nullable=true)
     */
    protected $slot;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->substitutes = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function isAsynchronous()
    {
        return $this->asynchronous;
    }

    /**
     * @param bool|string $asynchronous
     */
    public function setAsynchronous($asynchronous)
    {
        $this->asynchronous = $asynchronous;
    }

    /**
     * Set the action.
     *
     * @param string $action
     *
     * @throws \Exception The action is not valid
     */
    public function setAction($action)
    {
        //test validity of the action
        if ($action !== self::ACTION_CREATE && $action !== self::ACTION_OVERWRITE && $action !== self::ACTION_DELETE) {
            throw new \Exception('The action of the widget map is not valid. Action: ['.$action.']');
        }

        $this->action = $action;
    }

    /**
     * Get the action.
     *
     * @return string The action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return [Widget]
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * @param Widget $widget
     *
     * @return $this
     */
    public function addWidget(Widget $widget)
    {
        $this->widgets[] = $widget;

        return $this;
    }

    /**
     * @param [Widget] $widgets
     *
     * @return $this
     */
    public function setWidgets($widgets)
    {
        $this->widgets = $widgets;

        return $this;
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param View $view
     */
    public function setView(View $view)
    {
        $this->view = $view;
    }

    /**
     * Get the current View context.
     *
     * @return View
     */
    public function getContextualView()
    {
        return $this->contextualView;
    }

    /**
     * Store the current View context.
     *
     * @param View $contextualView
     *
     * @return $this
     */
    public function setContextualView(View $contextualView)
    {
        $this->contextualView = $contextualView;

        return $this;
    }

    /**
     * @return WidgetMap
     */
    public function getReplaced()
    {
        return $this->replaced;
    }

    /**
     * @param WidgetMap $replaced
     */
    public function setReplaced($replaced)
    {
        if ($replaced) {
            $replaced->addSubstitute($this);
        }
        $this->replaced = $replaced;
    }

    /**
     * @return string
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * @param string $slot
     */
    public function setSlot($slot)
    {
        $this->slot = $slot;
    }

    /**
     * Return "after" and "before" children,
     * based on contextual View and its Templates.
     *
     * @return mixed
     */
    public function getChildren(View $view = null)
    {
        $positions = [self::POSITION_BEFORE, self::POSITION_AFTER];
        $children = [];
        foreach ($positions as $position) {

            //Position is null by default
            $children[$position] = null;

            //Pass through all current WidgetMap children for a given position
            foreach ($this->getChilds($position) as $_child) {
                //If child don't have a substitute for this View and Templates, this is the one
                if (null === $_child->getSubstituteForView($view)) {
                    $children[$position] = $_child;
                }
            }

            //If children has not been found for this position
            //and current WidgetMap is a substitute
            if (!$children[$position] && $this->getReplaced()) {
                //Pass through all replaced WidgetMap children for a given position
                foreach ($this->getReplaced()->getChilds($position) as $_child) {
                    //If child don't have a substitute for this View and Templates, this is the one
                    if (null === $_child->getSubstituteForView($view)) {
                        $children[$position] = $_child;
                    }
                }
            }
        }

        return $children;
    }

    /**
     * @return Collection
     */
    public function getChildrenRaw()
    {
        return $this->children;
    }

    /**
     * @param $position
     * @param View|null $view
     *
     * @return bool
     */
    public function hasChild($position, View $view = null)
    {
        foreach ($this->getChildren($view) as $child) {
            if ($child && $child->getPosition() === $position) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return WidgetMap|null
     */
    public function getChild($position)
    {
        $child = null;
        foreach ($this->children as $_child) {
            if ($_child && $_child->getPosition() == $position) {
                $child = $_child;
            }
        }

        return $child;
    }

    /**
     * Return all children from contextual View (already loaded WidgetMaps).
     *
     * @return WidgetMap[]
     */
    public function getChilds($position)
    {
        $childsWidgetMaps = [];
        $viewWidgetMaps = $this->getContextualView()->getWidgetMapsForViewAndTemplates();

        foreach ($viewWidgetMaps as $viewWidgetMap) {
            if ($viewWidgetMap->getParent() == $this && $viewWidgetMap->getPosition() == $position) {
                $childsWidgetMaps[] = $viewWidgetMap;
            }
        }

        return $childsWidgetMaps;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function removeChildren()
    {
        foreach ($this->children as $child) {
            $this->removeChild($child);
        }
    }

    /**
     * @param WidgetMap $child
     */
    public function addChild($child)
    {
        $this->children->add($child);
    }

    /**
     * @param WidgetMap $child
     */
    public function removeChild($child)
    {
        $this->children->removeElement($child);
    }

    /**
     * @return WidgetMap|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param null|WidgetMap $parent
     */
    public function setParent(WidgetMap $parent = null)
    {
        if ($this->parent) {
            $this->parent->removeChild($this);
        }
        if ($parent) {
            $parent->addChild($this);
        }
        $this->parent = $parent;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Return all substitutes from contextual View (already loaded WidgetMaps)
     * Ideally must return only one WidgetMap per View.
     *
     * @return WidgetMap[]
     */
    public function getSubstitutes()
    {
        $substitutesWidgetMaps = [];
        $viewWidgetMaps = $this->getContextualView()->getWidgetMapsForViewAndTemplates();

        foreach ($viewWidgetMaps as $viewWidgetMap) {
            if ($viewWidgetMap->getReplaced() == $this) {
                $substitutesWidgetMaps[] = $viewWidgetMap;
            }
        }

        return $substitutesWidgetMaps;
    }

    /**
     * Return substitute if used in View.
     *
     * @return WidgetMap|null
     */
    public function getSubstituteForView(View $view)
    {
        foreach ($this->getSubstitutes() as $substitute) {
            if ($substitute->getView() === $view) {
                return $substitute;
            }
        }
    }

    /**
     * Return all Substitutes (not based on contextual View).
     *
     * @return ArrayCollection
     */
    public function getAllSubstitutes()
    {
        return $this->substitutes;
    }

    /**
     * @param WidgetMap $substitute
     */
    public function addSubstitute(WidgetMap $substitute)
    {
        $this->substitutes->add($substitute);
    }

    /**
     * @param [WidgetMap] $substitutes
     */
    public function setSubstitutes($substitutes)
    {
        $this->substitutes = $substitutes;
    }

    /**
     * @deprecated
     *
     * @return Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @deprecated
     *
     * @param Widget $widget
     *
     * @return WidgetMap
     */
    public function setWidget(Widget $widget = null)
    {
        $this->widget = $widget;

        return $this;
    }
}
