<?php

/* modules/contrib/slick/templates/slick-wrapper.html.twig */
class __TwigTemplate_16bce92cc50f7544a182c704f865747bc405aa773b7243c5e4be238326c5cb70 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $tags = array("set" => 16, "for" => 30, "spaceless" => 35, "if" => 36);
        $filters = array("clean_class" => 19, "replace" => 25, "without" => 37);
        $functions = array();

        try {
            $this->env->getExtension('Twig_Extension_Sandbox')->checkSecurity(
                array('set', 'for', 'spaceless', 'if'),
                array('clean_class', 'replace', 'without'),
                array()
            );
        } catch (Twig_Sandbox_SecurityError $e) {
            $e->setSourceContext($this->getSourceContext());

            if ($e instanceof Twig_Sandbox_SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

        // line 16
        $context["classes"] = array(0 => "slick-wrapper", 1 => (($this->getAttribute(        // line 18
($context["settings"] ?? null), "nav", array())) ? ("slick-wrapper--asnavfor") : ("")), 2 => (($this->getAttribute(        // line 19
($context["settings"] ?? null), "skin", array())) ? (("slick-wrapper--" . \Drupal\Component\Utility\Html::getClass($this->getAttribute(($context["settings"] ?? null), "skin", array())))) : ("")), 3 => (($this->getAttribute(        // line 20
($context["settings"] ?? null), "skin_thumbnail", array())) ? (("slick-wrapper--" . \Drupal\Component\Utility\Html::getClass($this->getAttribute(($context["settings"] ?? null), "skin_thumbnail", array())))) : ("")), 4 => (($this->getAttribute(        // line 21
($context["settings"] ?? null), "vertical", array())) ? ("slick-wrapper--v") : ("")), 5 => (($this->getAttribute(        // line 22
($context["settings"] ?? null), "vertical_tn", array())) ? ("slick-wrapper--v-tn") : ("")), 6 => (($this->getAttribute(        // line 23
($context["settings"] ?? null), "thumbnail_position", array())) ? (("slick-wrapper--tn-" . \Drupal\Component\Utility\Html::getClass($this->getAttribute(($context["settings"] ?? null), "thumbnail_position", array())))) : ("")), 7 => ((twig_in_filter("over", $this->getAttribute(        // line 24
($context["settings"] ?? null), "thumbnail_position", array()))) ? ("slick-wrapper--tn-overlay") : ("")), 8 => ((twig_in_filter("over", $this->getAttribute(        // line 25
($context["settings"] ?? null), "thumbnail_position", array()))) ? (("slick-wrapper--tn-" . twig_replace_filter($this->getAttribute(($context["settings"] ?? null), "thumbnail_position", array()), array("over-" => "")))) : ("")));
        // line 28
        echo "
";
        // line 29
        ob_start();
        // line 30
        echo "  ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["items"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
            // line 31
            echo "    ";
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $context["item"], "html", null, true));
            echo "
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        $context["content"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
        // line 34
        echo "
";
        // line 35
        ob_start();
        // line 36
        echo "  ";
        if ($this->getAttribute(($context["settings"] ?? null), "nav", array())) {
            // line 37
            echo "    <div";
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, twig_without($this->getAttribute(($context["attributes"] ?? null), "addClass", array(0 => ($context["classes"] ?? null)), "method"), "id"), "html", null, true));
            echo ">
      ";
            // line 38
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, ($context["content"] ?? null), "html", null, true));
            echo "
    </div>
  ";
        } else {
            // line 41
            echo "    ";
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, ($context["content"] ?? null), "html", null, true));
            echo "
  ";
        }
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
    }

    public function getTemplateName()
    {
        return "modules/contrib/slick/templates/slick-wrapper.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  92 => 41,  86 => 38,  81 => 37,  78 => 36,  76 => 35,  73 => 34,  63 => 31,  58 => 30,  56 => 29,  53 => 28,  51 => 25,  50 => 24,  49 => 23,  48 => 22,  47 => 21,  46 => 20,  45 => 19,  44 => 18,  43 => 16,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "modules/contrib/slick/templates/slick-wrapper.html.twig", "/var/www/AED/modules/contrib/slick/templates/slick-wrapper.html.twig");
    }
}
