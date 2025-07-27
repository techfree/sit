from langgraph.graph import StateGraph, MessagesState
from agent_0 import agent_exclusion_check
from agent import agent_eligibility
import json

# Fonction de routage conditionnel après exclusion_check
def check_exclusion_condition(state):
    try:
        last_response = state["messages"][-1].content
        parsed = json.loads(last_response)
        if parsed.get("excluded") is True:
            return "end_route"
        return "eligibility_check"
    except Exception as e:
        print("Erreur de parsing dans check_exclusion_condition:", e)
        return "eligibility_check"

# Création du graphe
builder = StateGraph(MessagesState)

# Déclaration des nœuds
builder.add_node("exclusion_check", agent_exclusion_check)
builder.add_node("eligibility_check", agent_eligibility)

# Point d'entrée
builder.set_entry_point("exclusion_check")

# Routage conditionnel basé sur le résultat de exclusion_check
builder.add_conditional_edges(
    "exclusion_check",
    check_exclusion_condition,
    {
        "end_route": "__end__",
        "eligibility_check": "eligibility_check"
    }
)

# Fin du graphe si eligibility_check est atteint
builder.add_edge("eligibility_check", "__end__")

# Compilation
graph = builder.compile()