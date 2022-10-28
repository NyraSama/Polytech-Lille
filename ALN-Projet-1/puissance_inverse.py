import math
import numpy as np
import scipy.linalg as nla

def _PuissanceInverse_ (M, mu):
    # Algorithme de la méthode de la puissance inverse
    # M est la matrice de données
    # mu est la valeur approché de la valeur propre de M dont on veut les vecteurs propres
    
    # On récupère le nombre de ligne de M
    l, c = M.shape
    
    # On calcul le vecteur propre associé à la valeur propre la plus proche de mu
    B= nla.inv(M - mu*np.eye(l))
    v= np.ones(l)
    v= (1/nla.norm(v, 2))*v
    for k in range(3):
        w= np.dot(B,v)
        v= (1/nla.norm(w, 2))*w
        
    # On retourne le vecteur propre
    return v